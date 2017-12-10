<?php

namespace Aptenex\Upp\Parser\Structure;

class Condition
{

    const TYPE_DATE = 'date';
    const TYPE_NIGHTS = 'nights';
    const TYPE_WEEKS = 'weeks';
    const TYPE_WEEKDAYS = 'weekdays';
    const TYPE_BOOKING_DAYS = 'booking_days';
    const TYPE_LUNAR_PHASE = 'lunar_phase';
    const TYPE_GUESTS = 'guests';

    public static $dateBasedConditions = [
        self::TYPE_DATE,
        self::TYPE_WEEKDAYS
    ];

    public static $unitBasedConditions = [
        self::TYPE_GUESTS,
        self::TYPE_NIGHTS,
        self::TYPE_WEEKS,
        self::TYPE_BOOKING_DAYS
    ];

    /**
     * @var string
     */
    protected $type;

    /**
     * If inverted, then the conditions boolean will be reversed
     *
     * @var bool
     */
    protected $inverse = false;

    /**
     * @var bool
     */
    protected $modifyRatePerUnit = true;

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
     * @return boolean
     */
    public function isInverse()
    {
        return $this->inverse;
    }

    /**
     * @param boolean $inverse
     */
    public function setInverse($inverse)
    {
        $this->inverse = $inverse;
    }

    /**
     * @return boolean
     */
    public function isModifyRatePerUnit()
    {
        return $this->modifyRatePerUnit;
    }

    /**
     * @param boolean $modifyRatePerUnit
     */
    public function setModifyRatePerUnit($modifyRatePerUnit)
    {
        $this->modifyRatePerUnit = (bool)$modifyRatePerUnit;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return [
            'type'              => $this->getType(),
            'inverse'           => $this->isInverse(),
            'modifyRatePerUnit' => $this->isModifyRatePerUnit()
        ];
    }

}