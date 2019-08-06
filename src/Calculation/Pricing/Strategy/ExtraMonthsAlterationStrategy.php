<?php

namespace Aptenex\Upp\Calculation\Pricing\Strategy;

use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\Structure\Rate;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Calculation\ControlItem\ControlItemInterface;
use Aptenex\Upp\Util\ArrayUtils;

class ExtraMonthsAlterationStrategy implements PriceAlterationInterface
{

    public function canAlter(PricingContext $context, ControlItemInterface $controlItem, FinalPrice $fp)
    {
        $matchedNightsCount = count($controlItem->getMatchedNights());

        // Override the matched nights to the total nights due to this config change
        if ($fp->getCurrencyConfigUsed()->getDefaults()->isExtraNightAlterationStrategyUseGlobalNights()) {
            $matchedNightsCount = $fp->getStay()->getNoNights();
        }

        $months = floor($matchedNightsCount / 30);

        $rateConfig = $controlItem->getControlItemConfig()->getRate();

        if (is_null($rateConfig->getStrategy())) {
            return false;
        }

        if (is_null($rateConfig->getStrategy()->getExtraMonthsAlteration())) {
            return false;
        }

        $extraMonthsAlteration = $controlItem->getControlItemConfig()->getRate()->getStrategy()->getExtraMonthsAlteration();

        // We need to figure out if one of the brackets match, if it does we can proceed for the alteration#

        $be = new BracketsEvaluator();

        if (!$be->hasAtLeastOneMatch($extraMonthsAlteration->getBrackets(), $months)) {
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

        $months = floor($matchedNightsCount / 30);

        $rateConfig = $controlItem->getControlItemConfig()->getRate();
        $extraMonthsAlteration = $rateConfig->getStrategy()->getExtraMonthsAlteration();

        $be = new BracketsEvaluator();

        $bracketDayValueMap = $be->retrieveExtraNightsDiscountValues($extraMonthsAlteration->getBrackets(), $months);

        // We need to sort this in case a lower bracket is added after a high one with
        // extraNightsAlterationStrategyUseGlobalNights being enabled as this causes issues
        ksort($bracketDayValueMap);

        if (empty($bracketDayValueMap)) {
            return; // No brackets so no point in looping
        }

        // Now we need to loop through and evaluate based on the present options
        // Let's get some various figures early on though

        $bracketData = $be->expandBrackets($extraMonthsAlteration->getBrackets(), $months, true);

        $lastBracketValue = ArrayAccess::getLastElement($bracketDayValueMap);

        $lastMatchingBracket = null;

        foreach ($matchedNightsList as $nightIndex => $night) {
            $nightNum = $nightIndex + 1;

            // This nightNum needs to be converted into the monthNum for the sake of
            // comparing, then we need to divide the 'month' bracket rate into each night
            $monthNum = (int)floor($nightIndex / 30) + 1;

            if (isset($bracketData[$monthNum])) {
                $lastMatchingBracket = $bracketData[$monthNum];
            }

            if (!$extraMonthsAlteration->isApplyToTotal() && !array_key_exists($monthNum, $bracketDayValueMap)) {
                // So if the applyToTotal is enabled, skip this check as we re-check if the make previous days
                // same rate if that is the case. If previous day same rate is not enabled - check the key
                // and apply!
                continue;
            }

            if ($extraMonthsAlteration->isMakePreviousMonthsSameRate()) {
                $rate = $lastBracketValue;
            } else if (array_key_exists($monthNum, $bracketDayValueMap)) {
                $rate = $bracketDayValueMap[$monthNum];
            } else {
                continue; // Skip
            }

            if (is_null($rate)) {
                continue; // Do not alter if there is no match...
            }

            if ($extraMonthsAlteration->getCalculationMethod() === Rate::METHOD_FIXED) {
                // Percentage based
                $monetaryAmount = \Aptenex\Upp\Util\MoneyUtils::fromString($rate, $fp->getCurrency());
            } else {
                $monetaryAmount = $night->getCost()->multiply((float)$rate);
            }

            if ($nightNum > ($months * 30) && ((float)$extraMonthsAlteration->getExtraNightsRate()) > 0) {
                // We've moved onto extra nights now
                $nightRate = \Aptenex\Upp\Util\MoneyUtils::fromString(
                    $extraMonthsAlteration->getExtraNightsRate(),
                    $fp->getCurrency()
                );
            } else {
                // THIS APPLIES FOR WHOLE MONTH BLOCKS
                // All these monetary amounts right are actually the monthly totals
                // We need to allocate them now.
                $allocatedMonthlyRate = $monetaryAmount->allocateTo(30);
                $nightRate = $allocatedMonthlyRate[$nightNum % 30];
            }

            $night->addStrategy($extraMonthsAlteration);

            switch ($extraMonthsAlteration->getCalculationOperand()) {

                case Operand::OP_ADDITION:

                    $night->setCost($night->getCost()->add($nightRate));

                    break;

                case Operand::OP_SUBTRACTION:

                    $night->setCost($night->getCost()->subtract($nightRate));

                    break;

                case Operand::OP_EQUALS:
                default:
                    $night->setCost($nightRate);

            }

        }

        $controlItem->getRate()->setStrategyData([
            'lastMatchedBracket' => $lastMatchingBracket
        ]);

        // Now we need to override shit
        // First lets deal with damage deposit
        if (
            !is_null($lastMatchingBracket) &&
            isset($lastMatchingBracket['damageDepositOverride']) &&
            !empty($lastMatchingBracket['damageDepositOverride'])
        ) {
            $ddOverrideMoney = \Aptenex\Upp\Util\MoneyUtils::fromString(
                (float)$lastMatchingBracket['damageDepositOverride'],
                $fp->getCurrency()
            );

            $controlItem->getRate()->setDamageDepositOverride($ddOverrideMoney);
        }

    }

    /**
     * @param PricingContext       $context
     * @param ControlItemInterface $controlItem
     * @param FinalPrice           $fp
     *
     * @return null|void
     */
    public function postAlter(PricingContext $context, ControlItemInterface $controlItem, FinalPrice $fp)
    {
        $rateConfig = $controlItem->getControlItemConfig()->getRate();

        if (is_null($rateConfig->getStrategy())) {
            return;
        }

        if (is_null($rateConfig->getStrategy()->getExtraMonthsAlteration())) {
            return;
        }

        $extraMonthsAlteration = $rateConfig->getStrategy()->getExtraMonthsAlteration();

        $stratData = $controlItem->getRate()->getStrategyData();
        $lastMatchedBracket = ArrayUtils::getNestedArrayValue('lastMatchedBracket', $stratData);

        // Now we can deal with the deposit override
        if (
            !empty($extraMonthsAlteration->getNumberOfMonthsDeposit()) &&
            ((int) $extraMonthsAlteration->getNumberOfMonthsDeposit()) > 0
        ) {
            // Ok, we want to override the deposit now - lets calculate the correct figure.
            // We need to find out the last matched bracket amount
            $monthlyAmount = $controlItem->getControlItemConfig()->getRate()->getAmount();

            if (!is_null($lastMatchedBracket) && isset($lastMatchedBracket['amount'])) {
                $monthlyAmount = $extraMonthsAlteration->getNumberOfMonthsDeposit() * (float)$lastMatchedBracket['amount'];
            }

            $monetaryDepositTotal = \Aptenex\Upp\Util\MoneyUtils::fromString(
                $monthlyAmount,
                $fp->getCurrency()
            );

            $controlItem->getRate()->setDepositOverride($monetaryDepositTotal);
        }
    }

}