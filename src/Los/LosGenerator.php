<?php

namespace Los;

use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Exception\BaseException;
use Aptenex\Upp\Exception\CannotMatchRequestedDatesException;
use Aptenex\Upp\Exception\InvalidPriceException;
use Aptenex\Upp\Parser\Structure\Condition;
use Aptenex\Upp\Parser\Structure\PricingConfig;
use Aptenex\Upp\Upp;
use Aptenex\Upp\Util\DateUtils;
use Aptenex\Upp\Util\MoneyUtils;
use Los\Lookup\LookupDirector;

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
     * @param LookupDirector $ld
     * @param PricingConfig $config
     */
    public function generateLosRecords(LosOptions $options, LookupDirector $ld, PricingConfig $config)
    {
        $losRecords = new LosRecords();
        $losRecords->startTiming();
        // TODO:
        // - Changeover days
        // - Detect if period exists for that day for base date
        // - Catch upp exceptions
        // - other speedups

        $bookingDate = date('Y-m-d');

        $startStamp = strtotime($options->getStartDate()->format('Y-m-d'));
        $endStamp = strtotime($options->getEndDate()->format('Y-m-d'));

        $days = round(($endStamp - $startStamp) / 86400) + 1; // Missing a day so add 1

        $maxOccupancy = $ld->getMaxOccupancyLookup()->getMaxOccupancy();

        $pc = new PricingContext();
        $pc->setBookingDate($bookingDate);

        $losRecords->setMaxAmountOfPotentialUppRuns($maxOccupancy * $days * $options->getMaximumStayRateLength());

        $range = DateUtils::getDateRangeInclusive($options->getStartDate(), $options->getEndDate());

        foreach($config->getCurrencyConfigs() as $cc) {
            if ($options->hasSingleCurrency() && $options->getSingleCurrency() !== $cc->getCurrency()) {
                continue; // If we are doing a single currency, then skip every other currency
            }

            $pc->setCurrency($cc->getCurrency());

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

                $notAvailable = false;
                $previousRateSet = null;

                for ($g = 1; $g <= $maxOccupancy; $g++) {
                    $rates = [];

                    if ($g >= $perGuestChangesAt || $previousRateSet === null || $options->isForceFullGeneration()) {
                        // Because we start on 1 we need to add 1 (this can be done with an LTE operator
                        for ($i = 1; $i <= $dateMaxStay; $i++) {

                            if (($i < $minStay || $i > $dateMaxStay) && !$options->isForceFullGeneration()) {
                                $rates[] = 0;
                                continue; // No generation
                            }

                            $departureDate = date('Y-m-d', strtotime(sprintf(' +%s day', $i), strtotime($date)));

                            if (
                                ($ld->getAvailabilityLookup()->isAvailable($departureDate) === false ||
                                $ld->getChangeoverLookup()->canDepart($departureDate) === false) &&
                                 !$options->isForceFullGeneration()
                            ) {
                                $rates[] = 0;
                                $notAvailable = true;
                                break;
                            }

                            $pc->setGuests($g);
                            $pc->setDepartureDate($departureDate);

                            try {
                                $losRecords->setTimesUppRan($losRecords->getTimesUppRan() + 1);
                                $fp = $this->upp->generatePrice($pc, $config);

                                $rates[] = MoneyUtils::getConvertedAmount($fp->getTotal());
                            } catch (CannotMatchRequestedDatesException $ex) {
                                $rates[] = 0;
                            } catch (InvalidPriceException $ex) {
                                $rates[] = 0;
                            } catch (BaseException $ex) {
                                $rates[] = 0;
                            }
                        }

                        if (\count($rates) < $dateMaxStay) {
                            $rates = array_pad($rates, $dateMaxStay, 0);
                        }

                        $previousRateSet = $rates;
                    }

                    $losRecords->addLineEntry(
                        $cc->getCurrency(),
                        $date,
                        $g,
                        $previousRateSet
                    );
                }
            }

        }

        $losRecords->finishTiming();
        return $losRecords;
    }

    /**
     * @return Upp
     */
    public function getUpp(): Upp
    {
        return $this->upp;
    }

}