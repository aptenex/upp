<?php /** @noinspection NotOptimalIfConditionsInspection */

namespace Aptenex\Upp\Los\Generator;

require 'vendor/autoload.php';

use Aptenex\Upp\Exception\LosAvailabiltitySkippedException;
use Aptenex\Upp\Exception\LosChangeoverSkippedException;
use Aptenex\Upp\Los\Debug\DebugException;
use Aptenex\Upp\Los\Debug\Diagnostics;
use Aptenex\Upp\Los\LosOptions;
use Aptenex\Upp\Los\LosRecords;
use Aptenex\Upp\Upp;
use Aptenex\Upp\Util\DateUtils;
use Aptenex\Upp\Util\MoneyUtils;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Exception\BaseException;
use Aptenex\Upp\Parser\Structure\Condition;
use Aptenex\Upp\Parser\Structure\PricingConfig;
use Aptenex\Upp\Los\Lookup\LookupDirectorInterface;
use Aptenex\Upp\Exception\InvalidPriceException;
use Aptenex\Upp\Exception\CannotGenerateLosException;
use Aptenex\Upp\Exception\CannotMatchRequestedDatesException;
use DateInterval;

class LosGenerator implements LosGeneratorInterface
{
    public const MAX_EXCEPTIONS = 50;
    /**
     * @var Upp
     */
    private $upp;

    /**
     * @param Upp $upp
     */
    public function __construct(Upp $upp)
    {
        $this->upp = $upp;
    }

    /**
     * @param LosOptions $options
     * @param LookupDirectorInterface $ld
     * @param PricingConfig $pricingConfig
     *
     * @return LosRecords
     *
     * @throws CannotGenerateLosException
     * @throws BaseException
     */
    public function generateLosRecords(LosOptions $options, LookupDirectorInterface $ld, PricingConfig $pricingConfig): LosRecords
    {
        $forcedDebugExceptions = $exceptions = [];
        $cc = null;
        
        foreach ($pricingConfig->getCurrencyConfigs() as $ccItem) {
            if ($ccItem->getCurrency() === $options->getCurrency()) {
                $cc = $ccItem;
                break;
            }
        }

        if ($cc === null) {
            throw CannotGenerateLosException::withArgs(
                'Provided currency does not exist in PricingConfig',
                ['currency' => $options->getCurrency()]
            );
        }
        $losRecords = new LosRecords($cc->getCurrency());
        $losRecords->getMetrics()->startTiming();
        // TODO:
        // - Add a "patch" system if only the availability changes.. aka go through existing LOS records and modify them

        $bookingDate = $options->hasBookingDate() ? $options->getBookingDate()->format('Y-m-d') : date('Y-m-d');

        $startStamp = strtotime($options->getStartDate()->format('Y-m-d'));
        $endStamp = strtotime($options->getEndDate()->format('Y-m-d'));

        /** @noinspection SummerTimeUnsafeTimeManipulationInspection */
        $days = round(($endStamp - $startStamp) / 86400) + 1; // Missing a day so add 1

        $maxOccupancy = $ld->getMaxOccupancyLookup()->getMaxOccupancy();

        $pc = new PricingContext();
        $pc->setBookingDate($bookingDate);
        $pc->setCurrency($cc->getCurrency());
        $pc->setCalculationModes($options->getPricingContextCalculationMode());

        $losRecords->getMetrics()->setMaxPotentialRuns($maxOccupancy * $days * $options->getMaximumStayRateLength());

       
        $guestModifierCount = 0;
        $perGuestChangesAt = 0;
        $guestModifierCondition = null;
        foreach ($cc->getModifiers() as $modifierItem) {
            foreach ($modifierItem->getConditions() as $conditionItem) {
                if ($conditionItem->getType() === Condition::TYPE_GUESTS) {
                    /** @var Condition\GuestsCondition $conditionItem */
                    $guestModifierCount++;
                    $guestModifierCondition = $conditionItem;
                }
            }
        }

        if ($guestModifierCount === 1 && $guestModifierCondition !== null) {
            // Only set if only 1 guest modifier
            $perGuestChangesAt = $guestModifierCondition->getMinimum();
        }
    
        $range = DateUtils::getDateRangeInclusive($options->getStartDate(), $options->getEndDate());
        foreach ($range as $date) {
            // If the base date is not available then skip it
            if (!$options->isForceAllAvailabilitiesGeneration() &&
                $ld->getAvailabilityLookup()->isAvailable($date) === false) {
                // We need to know if we are forcing a debug on this.
                if ($this->isForcedDateDebug($date, $options->getForceDebugOnDate())) {
                    $ex = new LosAvailabiltitySkippedException(sprintf('No Availability for %s', $date));
                    $ex->setArgs(['date' => $date]);
                    $forcedDebugExceptions[] = $ex->toDebugExceptionArray();
                }
                continue;
            }
    
    
            // We need to see if we can arrive on this date - if not then skip
            if ( ! $options->isForceFullGeneration() && $ld->getChangeoverLookup()->canArrive($date) === false) {
                if ($this->isForcedDateDebug($date, $options->getForceDebugOnDate())) {
                    $ex = new LosChangeoverSkippedException(sprintf('Not an accepted changeover for %s', $date));
                    $ex->setArgs(['date' => $date]);
                    $forcedDebugExceptions[] = $ex->toDebugExceptionArray();
                }
                continue;
            }

            // Perform generation per date up to the designated max stay or pad it
            $minStay = $ld->getMinimumStayLookup()->getMinimumStay($date);
            $dateMaxStay = $ld->getMaximumStayLookup()->getMaximumStay($date);
            if ($dateMaxStay > $options->getMaximumStayRateLength()) {
                $dateMaxStay = $options->getMaximumStayRateLength();
            }

            $pc->setArrivalDate($date);

            // Now we need to see if there are ANY guest range modifiers in this pricing - if not
            // then we can generate the same rate for all guest rates - otherwise lets perform
            // some simple introspection - if we there is only 1 modifier for guest range
            // we can determine at what point it'll get modified and calculate accordingly

            $previousRateSet = null;
            $previousBaseRateSet = null;

            for ($g = 1; $g <= $maxOccupancy; $g++) {
                $rates = [];
                $baseRates = [];

                if (($g >= $perGuestChangesAt && $guestModifierCondition !== null) || $previousRateSet === null || $options->isForceFullGeneration()) {
                    // Because we start on 1 we need to add 1 (this can be done with an LTE operator

                    $hasBlockedAvailability = false;

                    for ($i = 1; $i <= $dateMaxStay; $i++) {

                        $departureDate = date('Y-m-d', strtotime(sprintf(' +%s day', $i), strtotime($date)));
                        // We need to make sure that the departure cannot depart after it has failed to do so,
                        // eg. 100,200,300,0,0,0,0,800 <- that would not work as you cannot stay in between there
                        // Perform a check to see if it has already been switched, to stop an array lookup
                        try {
                            // We aren't checking if the departureDate is available, we actually need to check if the PREVIOUS
                            // day is available.
                            $p = (new \DateTime($departureDate))
                                ->sub(new DateInterval('P1D'))
                                ->format('Y-m-d');
                        } catch (\Exception $e) {
                            $hasBlockedAvailability = true;
                            $p = $departureDate;
                        }
                        
                        
                        if(!$options->isForceAllAvailabilitiesGeneration()){
                            if ($hasBlockedAvailability === false && !$ld->getAvailabilityLookup()->isAvailable($p)) {
                                $hasBlockedAvailability = true;
        
                                if ($this->isForcedDateDebug($date, $options->getForceDebugOnDate())) {
                                    $ex = new LosAvailabiltitySkippedException('Cannot generate prices after blocked availability');
                                    $ex->setArgs(['arrival' => $date, 'departure' => $departureDate, 'stayLength' => $i]);
                                    $forcedDebugExceptions[] = $ex->toDebugExceptionArray();
                                }
                            }
    
                            if ($hasBlockedAvailability) {
                                $rates[] = 0;
                                $baseRates[] = 0;
        
                                continue;
                            }
                        }
                        
                        if (!$options->isForceFullGeneration()) {

                            if ($i < $minStay) {
                                $rates[] = 0;
                                $baseRates[] = 0;

                                if ($this->isForcedDateDebug($date, $options->getForceDebugOnDate())) {
                                    $ex = new LosAvailabiltitySkippedException(sprintf('Minimum Stay of %s is not satisfied for Stay length of %s', $minStay, $i));
                                    $ex->setArgs(['date' => $date, 'minStay' => $minStay, 'stayLength' => $i]);
                                    $forcedDebugExceptions[] = $ex->toDebugExceptionArray();
                                }

                                continue; // No generation
                            }

                            if ($i > $dateMaxStay) {
                                $rates[] = 0;
                                $baseRates[] = 0;

                                if ($this->isForcedDateDebug($date, $options->getForceDebugOnDate())) {
                                    $ex = new LosAvailabiltitySkippedException(sprintf('Maximum Stay of %s is exceeded with Stay length of %s', $minStay, $i));
                                    $ex->setArgs(['date' => $date, 'maxStay' => $minStay, 'stayLength' => $i]);
                                    $forcedDebugExceptions[] = $ex->toDebugExceptionArray();
                                }

                                continue; // No generation
                            }
                            
// I CAN"T SEE HOW THIS DOES ANYTHING? REMOVING FOR NOW..
//                            if (!$ld->getAvailabilityLookup()->isAvailable($p)) {
//                                $rates[] = 0;
//                                $baseRates[] = 0;
//
//                                if ($this->isForcedDateDebug($date, $options->getForceDebugOnDate())) {
//                                    $ex = new LosAvailabiltitySkippedException(sprintf('Availability lookup failed for availability on %s with departure of %s', $p, $departureDate));
//                                    $ex->setArgs(['date' => $date, 'availableDate' => $departureDate]);
//                                    $forcedDebugExceptions[] = $ex->toDebugExceptionArray();
//                                }
//
//                                continue;
//                            }

                            if (!$ld->getChangeoverLookup()->canDepart($departureDate)) {
                                $rates[] = 0;
                                $baseRates[] = 0;

                                if ($this->isForcedDateDebug($date, $options->getForceDebugOnDate())) {
                                    $ex = new LosChangeoverSkippedException(sprintf('Changeover not possible for departing on %s', $departureDate));
                                    $ex->setArgs(['date' => $date, 'departureDate' => $departureDate]);
                                    $forcedDebugExceptions[] = $ex->toDebugExceptionArray();
                                }

                                continue;
                            }
                        }

                        $pc->setGuests($g);
                        $pc->setDepartureDate($departureDate);

                        try {
                            $losRecords->getMetrics()->setTimesRan($losRecords->getMetrics()->getTimesRan() + 1);
                            
                            $fp = $this->upp->generatePrice($pc, $pricingConfig);
                            $rates[] = MoneyUtils::getConvertedAmount($fp->getTotal());
                            $baseRates[] = MoneyUtils::getConvertedAmount($fp->getBasePrice());
                        } catch (CannotMatchRequestedDatesException|InvalidPriceException|BaseException $ex) {
                            $rates[] = 0;
                            $baseRates[] = 0;
                            $args = [];
                            if ($ex instanceof BaseException) {
                                $args = $ex->getArgs();
                            }

                            if ($this->isForcedDateDebug($date, $options->getForceDebugOnDate())) {
                                $forcedDebugExceptions[] = (new DebugException($ex->getCode(), $ex->getMessage(), $ex->getFile(), $ex->getLine(), $args))->toArray();
                            } else if ($options->isDebugMode() && count($exceptions) < Diagnostics::MAX_ALLOWED_DEBUGGING_ITEMS) {
                                $exceptions[] = (new DebugException($ex->getCode(), $ex->getMessage(), $ex->getFile(), $ex->getLine(), $args))->toArray();
                            }
                            
                        }

                    }

                    if (\count($rates) < $dateMaxStay) {
                        $rates = array_pad($rates, $dateMaxStay, 0);
                        $baseRates = array_pad($baseRates, $dateMaxStay, 0);
                    }

                    $previousRateSet = $rates;
                    // $previousBaseRateSet = $baseRates;
                }
				
                // We do not need to use the baseRates, because of the strategy modes.
                $losRecords->addLineEntry(
                    $date,
                    $g,
                    $previousRateSet
                );
                
                // $previousRateSet = true; // We set to true. (Testing).
            }
        }

        $losRecords->getMetrics()->finishTiming();
        $exceptions = array_slice($exceptions, 0, Diagnostics::MAX_ALLOWED_DEBUGGING_ITEMS);
        
        if (!empty($forcedDebugExceptions)) {
            $losRecords->setDebug($forcedDebugExceptions);
        }
        if ($options->isDebugMode()) {
            $losRecords->setDebug(array_merge($forcedDebugExceptions, $exceptions));
        }
        // Save the options that were used to generate this LOS.
        $losRecords->setBuildOptions($options);

        return $losRecords;
    }

    private function isForcedDateDebug(string $date, $dateDebugs = []): bool
    {
        if ($dateDebugs === null || empty($dateDebugs)) {
            return false;
        }

        foreach ($dateDebugs as $pattern) {
            // Regex..
            if ((strpos($pattern, '/') === 0) && @preg_match($pattern, $date, $matches)) {
                return true;
            }
            if ($date === $pattern) {
                return true;
            }
        }

        return false;
    }


    /**
     * @return Upp
     */
    public function getUpp(): Upp
    {
        return $this->upp;
    }

}