<?php

namespace Aptenex\Upp\Los;

require 'vendor/autoload.php';

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

class LosGenerator
{

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
     */
    public function generateLosRecords(LosOptions $options, LookupDirectorInterface $ld, PricingConfig $pricingConfig): LosRecords
    {
        $cc = null;
        foreach($pricingConfig->getCurrencyConfigs() as $ccItem) {
            if ($ccItem->getCurrency() === $options->getCurrency()) {
                $cc = $ccItem;
                break;
            }
        }

        if ($cc === null) {
            throw new CannotGenerateLosException('Provided currency does not exist in PricingConfig');
        }

        $losRecords = new LosRecords($cc->getCurrency());
        $losRecords->getMetrics()->startTiming();
        // TODO:
        // - Add a "patch" system if only the availability changes.. aka go through existing LOS records and modify them

        $bookingDate = date('Y-m-d');

        $startStamp = strtotime($options->getStartDate()->format('Y-m-d'));
        $endStamp = strtotime($options->getEndDate()->format('Y-m-d'));

        $days = round(($endStamp - $startStamp) / 86400) + 1; // Missing a day so add 1

        $maxOccupancy = $ld->getMaxOccupancyLookup()->getMaxOccupancy();

        $pc = new PricingContext();
        $pc->setBookingDate($bookingDate);
        $pc->setCurrency($cc->getCurrency());
        $pc->setCalculationMode($options->getPricingContextMode());

        $losRecords->getMetrics()->setMaxPotentialRuns($maxOccupancy * $days * $options->getMaximumStayRateLength());

        $range = DateUtils::getDateRangeInclusive($options->getStartDate(), $options->getEndDate());

        $guestModifierCount = 0;
        $perGuestChangesAt = 0;
        $guestModifierCondition = null;
        foreach($cc->getModifiers() as $modifierItem) {
            foreach($modifierItem->getConditions() as $conditionItem) {
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

        foreach($range as $date) {
            // If the base date is not available then skip it
            if ($ld->getAvailabilityLookup()->isAvailable($date) === false && !$options->isForceFullGeneration()) {
                continue;
            }

            // We need to see if we can arrive on this date - if not then skip
            if ($ld->getChangeoverLookup()->canArrive($date) === false && !$options->isForceFullGeneration()) {
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
                    for ($i = 1; $i <= $dateMaxStay; $i++) {

                        if (($i < $minStay || $i > $dateMaxStay) && !$options->isForceFullGeneration()) {
                            $rates[] = 0;
                            continue; // No generation
                        }

                        $departureDate = date('Y-m-d', strtotime(sprintf(' +%s day', $i), strtotime($date)));

                        if (
                            (
                                $ld->getAvailabilityLookup()->isAvailable($departureDate) === false ||
                                $ld->getChangeoverLookup()->canDepart($departureDate) === false
                            ) &&
                            !$options->isForceFullGeneration()
                        ) {
                            $rates[] = 0;
                            continue;
                        }

                        $pc->setGuests($g);
                        $pc->setDepartureDate($departureDate);

                        try {
                            $losRecords->getMetrics()->setTimesRan($losRecords->getMetrics()->getTimesRan() + 1);
                            $fp = $this->upp->generatePrice($pc, $pricingConfig);

                            $rates[] = MoneyUtils::getConvertedAmount($fp->getTotal());
                            $baseRates[] = MoneyUtils::getConvertedAmount($fp->getBasePrice());
                        } catch (CannotMatchRequestedDatesException $ex) {
                            $rates[] = 0;
                            $baseRates[] = 0;
                        } catch (InvalidPriceException $ex) {
                            $rates[] = 0;
                            $baseRates[] = 0;
                        } catch (BaseException $ex) {
                            $rates[] = 0;
                            $baseRates[] = 0;
                        }

                    }

                    if (\count($rates) < $dateMaxStay) {
                        $rates = array_pad($rates, $dateMaxStay, 0);
                        $baseRates = array_pad($baseRates, $dateMaxStay, 0);
                    }

                    $previousRateSet = $rates;
                    $previousBaseRateSet = $baseRates;
                }

                $losRecords->addLineEntry(
                    $date,
                    $g,
                    $previousRateSet,
                    $previousBaseRateSet
                );
            }
        }

        $losRecords->getMetrics()->finishTiming();

        return $losRecords;
    }

    /**
     * @param PricingConfig $config
     */
    private function removeMandatoryFeesAndTaxes(PricingConfig $config): void
    {
    }

    /**
     * @return Upp
     */
    public function getUpp(): Upp
    {
        return $this->upp;
    }

}