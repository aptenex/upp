<?php

namespace Aptenex\Upp\Calculation\Pricing;

use Aptenex\Upp\Helper\LanguageTools;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Parser\Structure\Condition;
use Aptenex\Upp\Calculation\AdjustmentAmount;
use Aptenex\Upp\Calculation\ControlItem\Modifier;
use Aptenex\Upp\Calculation\ControlItem\ControlItemInterface;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Parser\Structure\Operator;
use Aptenex\Upp\Util\MoneyUtils;
use Money\Money;

class RatePerConditionalUnitCalculator
{

    /**
     * @param \Aptenex\Upp\Calculation\Condition\Condition $condition
     * @return bool
     */
    public function canDetermineUnit(\Aptenex\Upp\Calculation\Condition\Condition $condition)
    {
        return in_array($condition->getConditionConfig()->getType(), Condition::$unitBasedConditions, true);
    }

    /**
     * @param PricingContext                               $context
     * @param \Aptenex\Upp\Calculation\Condition\Condition $condition
     * @param Modifier $modifier
     *
     * @return ConditionalUnitResult
     */
    public function determineUnits(PricingContext $context, \Aptenex\Upp\Calculation\Condition\Condition $condition, Modifier $modifier)
    {
        $cur = new ConditionalUnitResult();

        $config = $condition->getConditionConfig();

        if ($config instanceof Condition\GuestsCondition) {

            // Since we know this is a valid condition the guests must be above the minimum criteria and lower
            // than maximum, therefore we can just do total_guests - minimum_guests

            // Make it inclusive
            if ((int) $context->getGuests() === $config->getMinimum()) {
                $extraGuests = 1;
            } else {
                $extraGuests = ((int) $context->getGuests() - $config->getMinimum()) + 1;

                if ((int) $config->getMinimum() === 0) {
                    $extraGuests = $context->getGuests();
                }
            }

            $cur->setUnits($extraGuests);
            $cur->setUnitDescription('GUEST_UNIT');

        } else if ($config instanceof Condition\BookingDaysCondition) {

            // Since we know this is a valid condition the guests must be above the minimum criteria and lower
            // than maximum, therefore we can just do total_guests - minimum_guests

            // Make it inclusive
            if ((int) $context->getNoDaysBeforeArrival() === $config->getMinimum()) {
                $extraDays = 1;
            } else {
                $extraDays = (int) $context->getNoDaysBeforeArrival() - $config->getMinimum();
            }

            $cur->setUnits($extraDays);
            $cur->setUnitDescription('DAYS_BEFORE_ARRIVAL_UNIT');

        } else if ($config instanceof Condition\NightsCondition) {

            $noNights = (int) $context->getNoNights();

            if ($modifier->getConditions()->hasDateBasedCondition()) {
                // We need to get the matched nights from this condition instead
                $noNights = count($modifier->getMatchedNights());
            }

            // Same principal as above
            // Make it inclusive
            if ($noNights === ((int) $config->getMinimum())) {
                $extraNights = 1;
            } else {
                $extraNights = $noNights - $config->getMinimum();
            }

            $cur->setUnits($extraNights);
            $cur->setUnitDescription('NIGHT_UNIT');

        } else if ($config instanceof Condition\WeeksCondition) {

            $noNights = $context->getNoNights();
            if ($modifier->getConditions()->hasDateBasedCondition()) {
                // We need to get the matched nights from this condition instead
                $noNights = count($modifier->getMatchedNights());
            }

            $noWeeks = (int) ceil($noNights / 7);

            // Same principal as above
            // Make it inclusive
            if ($noWeeks === ((int) $config->getMinimum())) {
                $extraWeeks = 1;
            } else {
                $extraWeeks = $noWeeks - $config->getMinimum();
            }

            $cur->setUnits($extraWeeks);
            $cur->setUnitDescription('WEEK_UNIT');

        } else if ($config instanceof Condition\MonthsCondition) {

            $noNights = $context->getNoNights();
            if ($modifier->getConditions()->hasDateBasedCondition()) {
                // We need to get the matched nights from this condition instead
                $noNights = count($modifier->getMatchedNights());
            }

            $noMonths = (int) floor($noNights / 30);

            // Same principal as above
            // Make it inclusive
            if ($noMonths === ((int) $config->getMinimum())) {
                $noMonths = 1;
            } else {
                $noMonths = $noMonths - $config->getMinimum();
            }

            $cur->setUnits($noMonths);
            $cur->setUnitDescription('MONTH_UNIT');

        } else if ($config instanceof Condition\WeekdaysCondition) {
            $cur->setUnits(count($condition->getDatesMatched()));
            $cur->setUnitDescription('DAY_OF_WEEK_UNIT');
        }

        return $cur;
    }

    /**
     * @param FinalPrice $fp
     * @param Modifier|ControlItemInterface $modifier
     */
    public function applyConditionalRateModifications(FinalPrice $fp, Modifier $modifier): void
    {
        // We will add these to adjustments...
        /** @var \Aptenex\Upp\Parser\Structure\Modifier $controlItem */
        $controlItem = $modifier->getControlItemConfig();
        $rateConfig = $modifier->getControlItemConfig()->getRate();

        $isModifierDiscount = $rateConfig->getCalculationOperator() === Operator::OP_SUBTRACTION;

        $totalConditions = 0;
        $applyPerUnitConditions = 0;

        $calculationSourceAmount = $this->calculateSourceAmount($fp, $modifier);

        if ($rateConfig->getCalculationMethod() === \Aptenex\Upp\Parser\Structure\Rate::METHOD_PERCENTAGE) {
            $amount = $calculationSourceAmount->multiply($rateConfig->getAmount());
        } else {
            $amount = MoneyUtils::fromString($rateConfig->getAmount(), $fp->getCurrency());
        }

        $description = $controlItem->getDescription();

        // If there are no conditions then we can just apply the modifier instantly
        if (count($modifier->getConditions()->getConditions()) === 0) {
            $fp->addAdjustment(new AdjustmentAmount(
                $amount,
                strtoupper(trim(str_replace(' ', '_', $description))),
                $description,
                $rateConfig->getCalculationOperator(),
                AdjustmentAmount::TYPE_MODIFIER,
                $controlItem->getPriceGroup(),
                $controlItem->getSplitMethod(),
                $controlItem->isHidden(),
                $modifier
            ));

            return;
        }


        $totalUnitAmount = null;
        $totalString = $description;
        $nonNightUnits = null;
        $nightUnits = null;

        foreach($modifier->getConditions()->getConditions() as $condition) {
            if (!$this->canDetermineUnit($condition)) {
                // If we cannot determine a unit at all since it might be a date range condition then don't even
                // add to the total conditions as this date range should not affect the total price
                continue;
            }

            $totalConditions++;
            if ($condition->getConditionConfig()->isModifyRatePerUnit() && $this->canDetermineUnit($condition) && !$isModifierDiscount) {
                $applyPerUnitConditions++;
                $result = $this->determineUnits($fp->getContextUsed(), $condition, $modifier);

                if ($result->getUnits() <= 0) {
                    continue; // Cannot multiple by 0 and lower
                }

                // Check if it has been set yet
                if (is_null($totalUnitAmount)) {
                    $totalUnitAmount = $result->getUnits();
                } else {
                    $totalUnitAmount *= $result->getUnits();
                }

                if ($condition->getConditionConfig()->getType() !== Condition::TYPE_NIGHTS) {
                    if (is_null($nonNightUnits)) {
                        $nonNightUnits = $result->getUnits();
                    } else {
                        $nonNightUnits *= $result->getUnits();
                    }
                } else {
                    if (is_null($nonNightUnits)) {
                        $nightUnits = $result->getUnits();
                    } else {
                        $nightUnits *= $result->getUnits();
                    }
                }

                $totalString .= vsprintf(' (%sx %s)', [
                    $result->getUnits(),
                    LanguageTools::transChoice($result->getUnitDescription(), $result->getUnits())
                ]);

                $description = vsprintf('%s (%sx %s)', [
                    $description,
                    $result->getUnits(),
                    LanguageTools::transChoice($result->getUnitDescription(), $result->getUnits())
                ]);
            }
        }

        $finalAdjustmentAmount = null;

        if ($nonNightUnits > 0 || (is_null($nonNightUnits) && !is_null($totalUnitAmount))) {
            $finalAdjustmentAmount = $amount->multiply($totalUnitAmount);
        } else if ($nightUnits > 0 && $totalConditions === 1 && !is_null($nightUnits)) {
            $finalAdjustmentAmount =  $amount->multiply($nightUnits);
        } else if ($totalConditions > $applyPerUnitConditions) {
            $finalAdjustmentAmount = $amount;
        } else if ($modifier->getConditions()->hasOnlyNonUnitBasedConditions()) {

            // if this is a date only based discount, check if we need to partially apply it
            if (
                $modifier->getConditions()->isOnlyDateBased() &&
                $fp->getCurrencyConfigUsed()->getDefaults()->isApplyDiscountsToPartialMatches()
            ) {
                $totalNights = count($fp->getStay()->getNights());
                $matchedNightsAmount = count($modifier->getMatchedNights());
                $amount = $amount->multiply($matchedNightsAmount / $totalNights);
            }

            $finalAdjustmentAmount = $amount;
        }

        if (!is_null($finalAdjustmentAmount)) {
            $fp->addAdjustment(new AdjustmentAmount(
                $finalAdjustmentAmount,
                strtoupper(trim(str_replace(' ', '_', $description))),
                $description,
                $rateConfig->getCalculationOperator(),
                AdjustmentAmount::TYPE_MODIFIER,
                $controlItem->getPriceGroup(),
                $controlItem->getSplitMethod(),
                $controlItem->isHidden(),
                $modifier
            ));
        }
    }

    public function calculateSourceAmount(FinalPrice $fp, Modifier $modifier): Money
    {
        $useModifierCalculationOrder = $fp
            ->getCurrencyConfigUsed()
            ->getDefaults()
            ->isModifiersUseCategorizedCalculationOrder()
        ;

        if ($useModifierCalculationOrder === false) {
            return $fp->getBasePrice();
        }

        /** @var \Aptenex\Upp\Parser\Structure\Modifier $modifierConfig */
        $modifierConfig = $modifier->getControlItemConfig();

        $calculationOrder = $modifierConfig->getCalculationOrderFromType();

        // Since this code will always be run in the same order from PricingGenerator
        // We can calculate the new "price source" from the previous calculated modifiers

        switch ($calculationOrder) {

            case \Aptenex\Upp\Parser\Structure\Modifier::CALCULATION_ORDER_BASE_PRICE:
                // Calculates off base price
                return $fp->getBasePrice();

            case \Aptenex\Upp\Parser\Structure\Modifier::CALCULATION_ORDER_DISCOUNTS:
                // Calculates off base price + base price modifiers
                // Potentially handled later on so keep existing functionality
                return $fp->getBasePrice();

            case \Aptenex\Upp\Parser\Structure\Modifier::CALCULATION_ORDER_EXTRAS_FEES:
                return $fp->getBasePrice()->add($this->calculatePreviousAdjustmentsTotal($fp, [
                    \Aptenex\Upp\Parser\Structure\Modifier::CALCULATION_ORDER_BASE_PRICE,
                ], true));

            case \Aptenex\Upp\Parser\Structure\Modifier::CALCULATION_ORDER_MANAGEMENT_FEES:
                return $fp->getBasePrice()->add($this->calculatePreviousAdjustmentsTotal($fp, [
                    \Aptenex\Upp\Parser\Structure\Modifier::CALCULATION_ORDER_EXTRAS_FEES,
                    \Aptenex\Upp\Parser\Structure\Modifier::CALCULATION_ORDER_BASE_PRICE
                ], true));

            case \Aptenex\Upp\Parser\Structure\Modifier::CALCULATION_ORDER_CLEANING:
                return $fp->getBasePrice()->add($this->calculatePreviousAdjustmentsTotal($fp, [
                    \Aptenex\Upp\Parser\Structure\Modifier::CALCULATION_ORDER_EXTRAS_FEES,
                    \Aptenex\Upp\Parser\Structure\Modifier::CALCULATION_ORDER_BASE_PRICE,
                    \Aptenex\Upp\Parser\Structure\Modifier::CALCULATION_ORDER_MANAGEMENT_FEES
                ], true));

            case \Aptenex\Upp\Parser\Structure\Modifier::CALCULATION_ORDER_TOTAL:
                return $fp->getBasePrice()->add($this->calculatePreviousAdjustmentsTotal($fp, [
                    \Aptenex\Upp\Parser\Structure\Modifier::CALCULATION_ORDER_EXTRAS_FEES,
                    \Aptenex\Upp\Parser\Structure\Modifier::CALCULATION_ORDER_BASE_PRICE,
                    \Aptenex\Upp\Parser\Structure\Modifier::CALCULATION_ORDER_MANAGEMENT_FEES,
                    \Aptenex\Upp\Parser\Structure\Modifier::CALCULATION_ORDER_CLEANING
                ], true));

            case \Aptenex\Upp\Parser\Structure\Modifier::CALCULATION_ORDER_TAX:
            default:
                return $fp->getBasePrice();

        }
    }

    private function calculatePreviousAdjustmentsTotal(FinalPrice $fp, $previousOrders = [], bool $includeDiscounts = false): Money
    {
        $amount = MoneyUtils::newMoney(0, $fp->getCurrency());

        foreach($fp->getAdjustments() as $adj) {
            if ($adj->getType() !== AdjustmentAmount::TYPE_MODIFIER) {
                continue;
            }

            if ($adj->getOperand() === Operator::OP_ADDITION) {
                /** @var \Aptenex\Upp\Parser\Structure\Modifier $adjModifierConfig */
                $adjModifierConfig = $adj->getControlItem()->getControlItemConfig();

                if (\in_array($adjModifierConfig->getCalculationOrderFromType(), $previousOrders, true)) {
                    $amount = $amount->add($adj->getAmount());
                }

            } else if ($includeDiscounts && $adj->getOperand() === Operator::OP_SUBTRACTION) {
                $amount = $amount->subtract($adj->getAmount());
            }
        }

        return $amount;
    }

}