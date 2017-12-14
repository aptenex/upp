<?php

namespace Aptenex\Upp\Calculation\Pricing\Strategy;

use Aptenex\Upp\Calculation\ControlItem\ControlItemInterface;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Calculation\Night;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Parser\Structure\Rate;
use Money\Money;

class DaysOfWeekAlterationStrategy implements PriceAlterationInterface
{

    public function canAlter(PricingContext $context, ControlItemInterface $controlItem, FinalPrice $fp)
    {
        $matchedNights = count($controlItem->getMatchedNights());
        $extraNights = $matchedNights % 7;
        $rateConfig = $controlItem->getControlItemConfig()->getRate();

        if (is_null($rateConfig->getStrategy())) {
            return false;
        }

        if (is_null($rateConfig->getStrategy()->getDaysOfWeekAlteration())) {
            return false;
        }

        if ($extraNights === 0) {
            return false;
        }

        if ($rateConfig->getType() !== Rate::TYPE_WEEKLY) {
            return false;
        }

        return true;
    }

    public function alterPrice(PricingContext $context, ControlItemInterface $controlItem, FinalPrice $fp)
    {
        $matchedNights = count($controlItem->getMatchedNights());
        $weeks = floor($matchedNights / 7);
        $extraNights = $matchedNights % 7;

        $rateConfig = $controlItem->getControlItemConfig()->getRate();
        $dowa = $rateConfig->getStrategy()->getDaysOfWeekAlteration();

        $days = array_values($controlItem->getMatchedNights());

        // WORK OUT THE WEEKLY PERCENTAGE OF THE REMAINING STAYS
        $extraNightRate = \Aptenex\Upp\Util\MoneyUtils::fromString(($rateConfig->getAmount() / 7), $fp->getCurrency());

        if ($dowa->hasUnmatchedNightAmount()) {
            if ($dowa->getCalculationMethod() === Rate::METHOD_PERCENTAGE) {
                $amount = $rateConfig->getAmount() * $dowa->getUnmatchedNightAmount();
            } else {
                $amount = $dowa->getUnmatchedNightAmount();
            }

            $extraNightRate = \Aptenex\Upp\Util\MoneyUtils::fromString($amount, $fp->getCurrency());
        }

        /** @var Night[] $applicableNights */
        $applicableNights = array_slice($days, -1 * $extraNights);

        // Since bracket can be limited to match a certain amount of elements we need to count how many nights have used it
        $usedBrackets = [];

        $findDayBracket = function ($needle, $dayBrackets)  {
            foreach($dayBrackets as $bracket) {
                if (in_array($needle, $bracket['days'], true)) {
                    return $bracket;
                }
            }

            return null;
        };

        // First we need to see what has matched regarding the brackets from the applicable nights
        // We need to match the nights to the brackets and assign a monetary value...

        $total = \Aptenex\Upp\Util\MoneyUtils::newMoney(0, $fp->getCurrency());

        foreach($applicableNights as $night) {
            $dayBracket = $findDayBracket(strtolower($night->getDate()->format("l")), $dowa->getBrackets());

            if (is_null($dayBracket)) {
                continue;
            }

            // Set up counter stuff
            if (!isset($usedBrackets[$dayBracket['_id']])) {
                $usedBrackets[$dayBracket['_id']] = [
                    'count' => 0,
                    'nights' => [],
                    'bracket' => $dayBracket
                ];
            }

            if ($usedBrackets[$dayBracket['_id']]['count'] === (int) $dayBracket['matchAmount']) {
                // Set the night cost to the extra night rate
                $night->setCost($extraNightRate);
            } else {
                $usedBrackets[$dayBracket['_id']]['count'] += 1;
                $usedBrackets[$dayBracket['_id']]['nights'][] = $night;

                // We can actually apply the per unit price now to the matched nights

                if ($dowa->getCalculationMethod() === Rate::METHOD_PERCENTAGE) {
                    $bracketAmount = \Aptenex\Upp\Util\MoneyUtils::fromString(($rateConfig->getAmount() * $dayBracket['amount']), $fp->getCurrency());
                } else {
                    $bracketAmount = \Aptenex\Upp\Util\MoneyUtils::fromString($dayBracket['amount'], $fp->getCurrency());
                }

                $night->setCost($bracketAmount);
            }

            $total = $total->add($night->getCost());
        }

        $weeklyRate = \Aptenex\Upp\Util\MoneyUtils::fromString($rateConfig->getAmount(), $fp->getCurrency());

        if ($dowa->isUseWeeklyPriceIfExceeded() && $total->greaterThan($weeklyRate)) {
            // Eg at 6 days the cost may be more than the week - if so, revert to the weekly cost
            $perNight = $weeklyRate->allocateTo(count($applicableNights));

            foreach($perNight as $index => $nightlyCost) {
                $applicableNights[$index]->setCost($nightlyCost);
            }
        }
    }

    public function postAlter(PricingContext $context, ControlItemInterface $controlItem, FinalPrice $fp)
    {
        // Do nothing
    }

}