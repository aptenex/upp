<?php

namespace Aptenex\Upp\Calculation\Pricing\Strategy;

use Aptenex\Upp\Calculation\ControlItem\ControlItemInterface;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Calculation\Night;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Parser\Structure\Rate;
use Money\Money;

class PartialWeekAlterationStrategy implements PriceAlterationInterface
{

    public function canAlter(PricingContext $context, ControlItemInterface $controlItem, FinalPrice $fp)
    {
        $matchedNights = count($controlItem->getMatchedNights());

        if ($fp->getCurrencyConfigUsed()->getDefaults()->isPartialWeekAlterationStrategyUseGlobalNights()) {
            $matchedNights = $fp->getStay()->getNoNights();
        }

        $weeks = floor($matchedNights / 7);
        $extraNights = $matchedNights % 7;

        if ($extraNights === 0) {
            return false;
        }

        $rateConfig = $controlItem->getControlItemConfig()->getRate();

        if (is_null($rateConfig->getStrategy())) {
            return false;
        }

        if (is_null($rateConfig->getStrategy()->getPartialWeekAlteration())) {
            return false;
        }

        if ($rateConfig->getType() !== Rate::TYPE_WEEKLY) {
            return false;
        }

        $weekOvercharge = $controlItem->getControlItemConfig()->getRate()->getStrategy()->getPartialWeekAlteration();

        if (!is_null($weekOvercharge->getMinimumWeekCount()) && $weeks < $weekOvercharge->getMinimumWeekCount()) {
            return false; // If weeks below the minimum skip
        }

        if (!is_null($weekOvercharge->getMaximumWeekCount()) && $weeks > $weekOvercharge->getMaximumWeekCount()) {
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
        $weekOvercharge = $rateConfig->getStrategy()->getPartialWeekAlteration();

        $be = new BracketsEvaluator();

        $bracketsExtraNights = $extraNights;
        if ($fp->getCurrencyConfigUsed()->getDefaults()->isPartialWeekAlterationStrategyUseGlobalNights()) {
            $bracketsExtraNights = $fp->getStay()->getNoNights() % 7;
        }

        $bracketsValue = $be->retrieveValue($weekOvercharge->getBrackets(), $bracketsExtraNights, true);

        if (is_null($bracketsValue)) {
            return; // Do not alter as we have no valid bracket
        }

        /*
         * We need to apply this percentage/fixed value to the extra days that do not make up to a week
         * This can be done by taking the days off the end of the days array and applying it to them
         * since the dates are in order.
         */

        $days = array_values($controlItem->getMatchedNights());

        /** @var Night[] $applicableNights */
        $applicableNights = array_slice($days, -1 * $extraNights);

        // WORK OUT THE WEEKLY PERCENTAGE OF THE REMAINING STAYS
        $weeklyRate = \Aptenex\Upp\Util\MoneyUtils::fromString($rateConfig->getAmount(), $fp->getCurrency());

        if ($weekOvercharge->getCalculationMethod() === Rate::METHOD_PERCENTAGE) {
            $weeklyRate = $weeklyRate->multiply($bracketsValue);
            $newNightlyValues = $weeklyRate->allocateTo($extraNights);
        } else {
            $bracketsWeeklyRate = \Aptenex\Upp\Util\MoneyUtils::fromString($bracketsValue, $fp->getCurrency());
            $newNightlyValues = $bracketsWeeklyRate->allocateTo($extraNights);
        }

        $counter = 0;
        foreach($applicableNights as $night) {

            $night->addStrategy($weekOvercharge);

            switch ($weekOvercharge->getCalculationOperand()) {

                case Operand::OP_ADDITION:

                    $night->setCost($night->getCost()->add($newNightlyValues[$counter]));

                    break;

                case Operand::OP_SUBTRACTION:

                    $night->setCost($night->getCost()->subtract($newNightlyValues[$counter]));

                    break;

                case Operand::OP_EQUALS:
                default:
                    $night->setCost($newNightlyValues[$counter]);

            }

            $counter++;
        }
    }

    public function postAlter(PricingContext $context, ControlItemInterface $controlItem, FinalPrice $fp)
    {
        // Do nothing
    }


}