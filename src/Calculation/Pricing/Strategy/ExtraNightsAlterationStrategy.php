<?php

namespace Aptenex\Upp\Calculation\Pricing\Strategy;

use Aptenex\Upp\Calculation\ControlItem\ControlItemInterface;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Parser\Structure\Rate;
use Money\Money;

class ExtraNightsAlterationStrategy implements PriceAlterationInterface
{

    public function canAlter(PricingContext $context, ControlItemInterface $controlItem, FinalPrice $fp)
    {
        $matchedNightsCount = count($controlItem->getMatchedNights());

        // Override the matched nights to the total nights due to this config change
        if ($fp->getCurrencyConfigUsed()->getDefaults()->isExtraNightAlterationStrategyUseGlobalNights()) {
            $matchedNightsCount = $fp->getStay()->getNoNights();
        }

        $rateConfig = $controlItem->getControlItemConfig()->getRate();

        if (is_null($rateConfig->getStrategy())) {
            return false;
        }

        if (is_null($rateConfig->getStrategy()->getExtraNightsAlteration())) {
            return false;
        }

        $extraNightsAlteration = $controlItem->getControlItemConfig()->getRate()->getStrategy()->getExtraNightsAlteration();

        // We need to figure out if one of the brackets match, if it does we can proceed for the alteration#

        $be = new BracketsEvaluator();

        if (!$be->hasAtLeastOneMatch($extraNightsAlteration->getBrackets(), $matchedNightsCount)) {
            return false;
        }

        return true;
    }

    public function alterPrice(PricingContext $context, ControlItemInterface $controlItem, FinalPrice $fp)
    {
        $matchedNightsCount = count($controlItem->getMatchedNights());
        $matchedNightsList = $controlItem->getMatchedNights();

        if ($fp->getCurrencyConfigUsed()->getDefaults()->isExtraNightAlterationStrategyUseGlobalNights()) {
            // Override the count so we will actually change the price on the relevant nights list
            $matchedNightsCount = $fp->getStay()->getNoNights();
        }

        $rateConfig = $controlItem->getControlItemConfig()->getRate();
        $extraNightsAlteration = $rateConfig->getStrategy()->getExtraNightsAlteration();

        $be = new BracketsEvaluator();

        $bracketDayValueMap = $be->retrieveExtraNightsDiscountValues($extraNightsAlteration->getBrackets(), $matchedNightsCount);

        // We need to sort this in case a lower bracket is added after a high one with
        // extraNightsAlterationStrategyUseGlobalNights being enabled as this causes issues
        ksort($bracketDayValueMap);

        if (empty($bracketDayValueMap)) {
            return; // No brackets so no point in looping
        }

        // Now we need to loop through and evaluate based on the present options
        // Let's get some various figures early on though

        $lastBracketValue = ArrayAccess::getLastElement($bracketDayValueMap);

        foreach($matchedNightsList as $nightIndex => $night) {
            $nightNum = $nightIndex + 1;

            if (!$extraNightsAlteration->isApplyToTotal() && !array_key_exists($nightNum, $bracketDayValueMap)) {
                // So if the applyToTotal is enabled, skip this check as we re-check if the make previous days
                // same rate if that is the case. If previous day same rate is not enabled - check the key
                // and apply!
                continue;
            }

            if ($extraNightsAlteration->isMakePreviousNightsSameRate()) {
                $rate = $lastBracketValue;
            } else if (array_key_exists($nightNum, $bracketDayValueMap)) {
                $rate = $bracketDayValueMap[$nightNum];
            } else {
                continue; // Skip
            }

            if (is_null($rate)) {
                continue; // Do not alter if there is no match...
            }

            if ($extraNightsAlteration->getCalculationMethod() === Rate::METHOD_FIXED) {
                // Percentage based
                $monetaryAmount = \Aptenex\Upp\Util\MoneyUtils::fromString($rate, $fp->getCurrency());
            } else {
                $monetaryAmount = $night->getCost()->multiply((float) $rate);
            }

            switch ($extraNightsAlteration->getCalculationOperand()) {

                case Operand::OP_ADDITION:

                    $night->setCost($night->getCost()->add($monetaryAmount));

                    break;

                case Operand::OP_SUBTRACTION:

                    $night->setCost($night->getCost()->subtract($monetaryAmount));

                    break;

                case Operand::OP_EQUALS:
                default:
                    $night->setCost($monetaryAmount);

            }
        }

    }

}