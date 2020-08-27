<?php

namespace Aptenex\Upp\Transformer;

use Aptenex\Upp\Models\DateRange;
use Aptenex\Upp\Models\SpecialDiscount\SpecialDiscountItem;
use Aptenex\Upp\Parser\Structure\Condition\BookingDaysCondition;
use Aptenex\Upp\Parser\Structure\Condition\DateCondition;
use Aptenex\Upp\Parser\Structure\Condition\MultiDateCondition;
use Aptenex\Upp\Parser\Structure\Modifier;
use Aptenex\Upp\Util\NumberUtils;

class SpecialDiscountTransformer
{

    /**
     * @param Modifier[] $modifiers
     * @return SpecialDiscountItem[]
     */
    public function transformSpecialDiscounts(array $modifiers): array
    {
        $items = [];

        foreach($modifiers as $modifier) {
            if ($modifier->satisfiesSpecialDiscountCriteria()) {
                $items[] = $this->transformModifier($modifier);
            }
        }

        return $items;
    }

    public function transformModifier(Modifier $modifier): SpecialDiscountItem
    {
        $item = new SpecialDiscountItem();

        $item->setName($modifier->getDescription());
        $item->setAmount($modifier->getRate()->getAmount());
        $item->setType(strtoupper($modifier->getRate()->getCalculationMethod()));

        $ranges = [];
        foreach($modifier->getConditions() as $condition) {
            if ($condition instanceof DateCondition) {
                $dr = new DateRange();

                $dr->setStartDate(new \DateTime($condition->getStartDate()));
                $dr->setEndDate(new \DateTime($condition->getEndDate()));

                $ranges[] = $dr;
            } else if ($condition instanceof MultiDateCondition) {
               foreach($condition->getDates() as $itemDateRange) {
                   $dr = new DateRange();

                   $dr->setStartDate(new \DateTime($itemDateRange['start']));
                   $dr->setEndDate(new \DateTime($itemDateRange['end']));

                   $ranges[] = $dr;
               }
            } else if ($condition instanceof BookingDaysCondition) {
                $item->setCategory(SpecialDiscountItem::DISCOUNT_CATEGORY_DAYS_BEFORE_ARRIVAL);
                $item->setMinimumDaysBeforeArrival(NumberUtils::clamp($condition->getMinimum(), 0, 3 * 365));
                $item->setMaximumDaysBeforeArrival(NumberUtils::clamp($condition->getMaximum(), 0, 3 * 365));
            }
        }

        $item->setDateRanges($ranges);

        $this->applyClassificationIfValid($item);

        return $item;
    }

    private function applyClassificationIfValid(SpecialDiscountItem $item): void
    {
        if ($item->getCategory() === SpecialDiscountItem::DISCOUNT_CATEGORY_DAYS_BEFORE_ARRIVAL) {

            if ($item->getMinimumDaysBeforeArrival() !== null && $item->getMaximumDaysBeforeArrival() === null) {
                $item->setClassification(SpecialDiscountItem::DISCOUNT_CLASSIFICATION_EARLY_BIRD);

                return;
            }

            if ($item->getMinimumDaysBeforeArrival() === null && $item->getMaximumDaysBeforeArrival() !== null) {
                $item->setClassification(SpecialDiscountItem::DISCOUNT_CLASSIFICATION_LAST_MINUTE);

                return;
            }

        }
    }

}