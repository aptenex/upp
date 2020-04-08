<?php

namespace Aptenex\Upp\Calculation\Pricing\Strategy;

use Aptenex\Upp\Calculation\ControlItem\ControlItemInterface;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Exception\InvalidPricingConfigException;
use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\Structure\ExtraNightsAlteration;
use Aptenex\Upp\Parser\Structure\Operator;
use Aptenex\Upp\Parser\Structure\Rate;
use Aptenex\Upp\Util\MoneyUtils;

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

        if ($rateConfig->getStrategy() === null) {
            return false;
        }

        if ($rateConfig->getStrategy()->getExtraNightsAlteration() === null) {
            return false;
        }

        $extraNightsAlteration = $controlItem->getControlItemConfig()->getRate()->getStrategy()->getExtraNightsAlteration();

        // We need to figure out if one of the brackets match, if it does we can proceed for the alteration#

        $be = new BracketsEvaluator();

        // Even if this has per guest per night enabled, we always match on the night first,
        // so this does not even matter if the guests array exists or not

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

        $bracketDayValueMap = $be->retrieveExtraNightsDiscountValues(
            $extraNightsAlteration->getBrackets(),
            $matchedNightsCount,
            $extraNightsAlteration->isEnablePerGuestPerNight() ? $context->getGuests() : null
        );

        // We need to sort this in case a lower bracket is added after a high one with
        // extraNightsAlterationStrategyUseGlobalNights being enabled as this causes issues
        ksort($bracketDayValueMap);

        if (empty($bracketDayValueMap)) {
            return; // No brackets so no point in looping
        }

        // Now we need to loop through and evaluate based on the present options
        // Let's get some various figures early on though

        foreach($matchedNightsList as $nightIndex => $night) {
            $nightNum = $nightIndex + 1;

            $value = $this->getNightlyValue(
                $nightNum,
                $context->getGuests(),
                MoneyUtils::getConvertedAmount($night->getCost()),
                $bracketDayValueMap,
                $extraNightsAlteration
            );

            if ($value !== null) {
                $night->addStrategy($extraNightsAlteration);

                $moneyValue = MoneyUtils::fromString($value, $night->getCost()->getCurrency());

                switch ($extraNightsAlteration->getCalculationOperator()) {

                    case Operator::OP_ADDITION:

                        $night->setCost($night->getCost()->add($moneyValue));

                        break;

                    case Operator::OP_SUBTRACTION:

                        $night->setCost($night->getCost()->subtract($moneyValue));

                        break;

                    case Operator::OP_EQUALS:
                    default:
                        $night->setCost($moneyValue);

                }
            }
        }

    }

    /**
     * @param int $nightNum
     * @param int $guestNum
     * @param float $baseNightlyCost
     * @param array $bracketDayValueMap
     * @param ExtraNightsAlteration $strategy
     *
     * @return float|null
     * @throws InvalidPricingConfigException
     */
    public function getNightlyValue(int $nightNum, int $guestNum, float $baseNightlyCost, array $bracketDayValueMap, ExtraNightsAlteration $strategy): ?float
    {
        $lastBracketValue = ArrayAccess::getLastElement($bracketDayValueMap);

        if (!\array_key_exists($nightNum, $bracketDayValueMap) && !$strategy->isApplyToTotal()) {
            // So if the applyToTotal is enabled, skip this check as we re-check if the make previous days
            // same rate if that is the case. If previous day same rate is not enabled - check the key
            // and apply!
            return null;
        }

        $rate = null;

        if ($strategy->isMakePreviousNightsSameRate()) {
            if ($strategy->isEnablePerGuestPerNight() && $guestNum > 0) {
                $rate = $this->getGuestValue($guestNum, $lastBracketValue);
            } else {
                $rate = $lastBracketValue;
            }
        } else if (array_key_exists($nightNum, $bracketDayValueMap)) {
            if ($strategy->isEnablePerGuestPerNight() && $guestNum > 0) {
                $rate = $this->getGuestValue($guestNum, $bracketDayValueMap[$nightNum]);
            } else {
                $rate = $bracketDayValueMap[$nightNum];
            }
        } else {
            return null; // Skip
        }

        if ($rate === null) {
            return null;
        }

        if ($strategy->getCalculationMethod() === Rate::METHOD_FIXED) {
            $monetaryAmount = $rate;
        } else {
            $monetaryAmount = $baseNightlyCost * (float) $rate;
        }

        return (float) $monetaryAmount;
    }

    private function getGuestValue(int $guestNum, $guestBracket, $default = 0)
    {
        if (isset($guestBracket[(string) $guestNum])) {
            return $guestBracket[(string) $guestNum];
        }

        if (isset($guestBracket['_default'])) {
            return $guestBracket['_default'];
        }

        throw new InvalidPricingConfigException('No default value specified on the guest bracket mapping');
    }

    public function postAlter(PricingContext $context, ControlItemInterface $controlItem, FinalPrice $fp)
    {
        // Do nothing
    }

}