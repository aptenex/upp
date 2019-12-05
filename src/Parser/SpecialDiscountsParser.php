<?php

namespace Aptenex\Upp\Parser;

use Aptenex\Upp\Calculation\AdjustmentAmount;
use Aptenex\Upp\Parser\Structure\Condition;
use Aptenex\Upp\Parser\Structure\Modifier;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Parser\Structure\Rate;
use Aptenex\Upp\Parser\Structure\StructureOptions;

class SpecialDiscountsParser extends ModifiersParser
{

    /**
     * @param array $modifiersArray
     * @param StructureOptions $options
     *
     * @return Modifier[]
     */
    public function parse(array $modifiersArray, StructureOptions $options): array
    {
        $parsedModifiers = parent::parse($modifiersArray, $options);

        $m = [];

        foreach($parsedModifiers as $modifier) {
            if (!$this->doesModifierSatisfySpecialDiscountCriteria($modifier)) {
                continue;
            }

            $m[] = $modifier;
        }

        return $m;
    }

    public function doesModifierSatisfySpecialDiscountCriteria(Modifier $modifier): bool
    {
        if ($modifier->getType() !== Modifier::TYPE_DISCOUNT) {
            return false;
        }

        if ($modifier->getPriceGroup() !== AdjustmentAmount::PRICE_GROUP_TOTAL) {
            return false;
        }

        if ($modifier->getRate()->getCalculationMethod() !== Rate::METHOD_PERCENTAGE) {
            return false;
        }

        if ($modifier->getRate()->getCalculationOperand() !== Operand::OP_SUBTRACTION) {
            return false;
        }

        if ($modifier->getRate()->getAmount() <= 0) {
            return false;
        }

        // Look at the conditions, we are essentially looking for two conditions and ignoring the distribution condition
        $validConditionCount = 0;

        $hasValidDateRangeCondition = false;
        $hasValidBookingDaysCondition = false;

        foreach($modifier->getConditions() as $condition) {
            if ($condition->getType() === Condition::TYPE_DISTRIBUTION) {
                continue;
            }

            $validConditionCount++;

            if ($condition->getType() === Condition::TYPE_DATE) {
                /** @var Condition\DateCondition $condition */
                if (!empty($condition->getStartDate()) && !empty($condition->getEndDate())) {
                    $hasValidDateRangeCondition = true;
                }
            } else if ($condition->getType() === Condition::TYPE_BOOKING_DAYS) {
                /** @var Condition\BookingDaysCondition $condition */
                // We need at least one value entered here
                if (!empty($condition->getMinimum()) || !empty($condition->getMaximum())) {
                    $hasValidBookingDaysCondition = true;
                }
            }
        }

        if ($validConditionCount !== 2) {
            return false;
        }

        if (!$hasValidBookingDaysCondition) {
            return false;
        }

        if (!$hasValidDateRangeCondition) {
            return false;
        }

        return true;
    }

}