<?php

namespace Aptenex\Upp\Parser\Structure;

use Aptenex\Upp\Calculation\AdjustmentAmount;

class Modifier extends AbstractControlItem implements ControlItemInterface
{

    public const TYPE_MODIFIER = 'modifier';

    public const TYPE_BASE_PRICE_FEE = 'base_price_fee';
    public const TYPE_EXTRA_GUEST_FEE = 'extra_guest_fee';

    public const TYPE_DISCOUNT = 'discount';

    public const TYPE_HOST_FEE = 'host_fee';
    public const TYPE_GENERAL_FEE = 'general_fee';
    public const TYPE_TOURISM_TAX = 'tourism_tax';
    public const TYPE_DESTINATION_FEE = 'destination_fee';
    public const TYPE_ENERGY_FEE = 'energy_fee';

    public const TYPE_BOOKING_FEE = 'booking_fee';
    public const TYPE_SERVICE_CHARGE = 'service_charge';
    public const TYPE_RESORT_FEE = 'resort_fee';

    public const TYPE_CLEANING = 'cleaning';

    public const TYPE_CARD_FEE = 'card_fee';

    public const TYPE_TAX = 'tax';

    public static $priceGroupBaseTypes = [
        self::TYPE_EXTRA_GUEST_FEE,
        self::TYPE_BASE_PRICE_FEE
    ];

    public const CALCULATION_ORDER_BASE_PRICE = 'CALCULATION_ORDER_BASE_PRICE';
    public const CALCULATION_ORDER_DISCOUNTS = 'CALCULATION_ORDER_DISCOUNTS';
    public const CALCULATION_ORDER_EXTRAS_FEES = 'CALCULATION_ORDER_EXTRAS_FEES';
    public const CALCULATION_ORDER_MANAGEMENT_FEES = 'CALCULATION_ORDER_MANAGEMENT_FEES';
    public const CALCULATION_ORDER_CLEANING = 'CALCULATION_ORDER_CLEANING';
    public const CALCULATION_ORDER_TOTAL = 'CALCULATION_ORDER_TOTAL';
    public const CALCULATION_ORDER_TAX = 'CALCULATION_ORDER_TAX';

    public static $modifierCalculationOrder = [
        self::CALCULATION_ORDER_BASE_PRICE => [
            self::TYPE_EXTRA_GUEST_FEE,
            self::TYPE_BASE_PRICE_FEE,
            self::TYPE_MODIFIER
        ],
        self::CALCULATION_ORDER_DISCOUNTS  => [
            self::TYPE_DISCOUNT
        ],
        self::CALCULATION_ORDER_EXTRAS_FEES => [
            self::TYPE_HOST_FEE,
            self::TYPE_GENERAL_FEE,
            self::TYPE_TOURISM_TAX,
            self::TYPE_DESTINATION_FEE,
            self::TYPE_ENERGY_FEE,
        ],
        self::CALCULATION_ORDER_MANAGEMENT_FEES => [
            self::TYPE_BOOKING_FEE,
            self::TYPE_SERVICE_CHARGE,
            self::TYPE_RESORT_FEE
        ],
        self::CALCULATION_ORDER_CLEANING => [
            self::TYPE_CLEANING
        ],
        self::CALCULATION_ORDER_TOTAL => [
            self::TYPE_CARD_FEE
        ],
        self::CALCULATION_ORDER_TAX => [
            self::TYPE_TAX
        ]
    ];

    /**
     * @var string
     */
    protected $type = self::TYPE_MODIFIER;

    /**
     * @var string
     */
    protected $splitMethod = SplitMethod::ON_TOTAL;

    /**
     * @var bool
     */
    protected $hidden = false;

    /**
     * @var bool
     */
    protected $priceGroup = AdjustmentAmount::PRICE_GROUP_TOTAL; // Default

    /**
     * @return null|string
     */
    public function getCalculationOrderFromType(): ?string
    {
        foreach(self::$modifierCalculationOrder as $calculationOrder => $modifierTypes) {
            foreach($modifierTypes as $type) {
                if ($type === $this->getType()) {
                    return $calculationOrder;
                }
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getSplitMethod()
    {
        return $this->splitMethod;
    }

    /**
     * @param string $splitMethod
     */
    public function setSplitMethod($splitMethod)
    {
        $this->splitMethod = $splitMethod;
    }

    /**
     * @return boolean
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * @param boolean $hidden
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * @return string
     */
    public function getPriceGroup()
    {
        return $this->priceGroup;
    }

    /**
     * @param string $priceGroup
     */
    public function setPriceGroup(string $priceGroup)
    {
        $this->priceGroup = $priceGroup;
    }

    /**
     * @return bool
     */
    public function isModifierTypeBasePrice(): bool
    {
        return in_array($this->getType(), self::$priceGroupBaseTypes, true);
    }

    public function isDiscount(): bool
    {
        return $this->getRate()->getCalculationOperand() === Operand::OP_SUBTRACTION;
    }

    public function satisfiesSpecialDiscountCriteria(): bool
    {
        if ($this->getType() !== self::TYPE_DISCOUNT && $this->getType() !== self::TYPE_MODIFIER) {
            return false;
        }

        if ($this->isHidden()) {
            return false;
        }

        if ($this->getPriceGroup() !== AdjustmentAmount::PRICE_GROUP_TOTAL) {
            return false;
        }

        if ($this->getRate()->getCalculationMethod() !== Rate::METHOD_PERCENTAGE) {
            return false;
        }

        if ($this->getRate()->getCalculationOperand() !== Operand::OP_SUBTRACTION) {
            return false;
        }

        if ($this->getRate()->getAmount() <= 0) {
            return false;
        }

        // Look at the conditions, we are essentially looking for two conditions and ignoring the distribution condition
        $validConditionCount = 0;

        $hasValidDateRangeCondition = false;
        $hasValidBookingDaysCondition = false;

        foreach ($this->getConditions() as $condition) {
            if ($condition->getType() === Condition::TYPE_DISTRIBUTION) {
                continue;
            }

            $validConditionCount++;

            if ($condition->getType() === Condition::TYPE_DATE) {
                /** @var Condition\DateCondition $condition */
                if (!empty($condition->getStartDate()) && !empty($condition->getEndDate())) {
                    $hasValidDateRangeCondition = true;
                }
            } else if ($condition->getType() === Condition::TYPE_MULTI_DATE) {
                /** @var Condition\MultiDateCondition $condition */
                if (!empty($condition->getDates())) {
                    $hasValidDateRangeCondition = true; // Multi-data is also supported
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

    /**
     * @return array
     */
    public function __toArray(): array
    {
        return array_replace(parent::__toArray(), [
            'type'        => $this->getType(),
            'splitMethod' => $this->getSplitMethod(),
            'hidden'      => $this->isHidden(),
            'priceGroup'  => $this->getPriceGroup()
        ]);
    }

}