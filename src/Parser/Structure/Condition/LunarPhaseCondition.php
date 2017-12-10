<?php

namespace Aptenex\Upp\Parser\Structure\Condition;

use Aptenex\Upp\Exception\InvalidPricingConfigException;
use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\Structure\Condition;

class LunarPhaseCondition extends Condition
{

    const BOOKING_DATE = 'booking_date';
    const ARRIVAL_DATE = 'arrival_date';
    const DEPARTURE_DATE = 'departure_date';

    /**
     * @var string[]
     */
    private $phases = [];

    /**
     * @var string
     */
    private $dateType = LunarPhaseCondition::BOOKING_DATE;

    /**
     * @var string[]
     */
    private static $dateTypeList = [
        self::BOOKING_DATE,
        self::ARRIVAL_DATE,
        self::DEPARTURE_DATE
    ];

    /**
     * @var string[]
     */
    private static $phaseList = [
        'new_moon',
        'waxing_crescent',
        'first_quarter',
        'waxing_gibbous',
        'full_moon',
        'waning_gibbous',
        'third_quarter',
        'waning_crescent'
    ];

    /**
     * @return \string[]
     */
    public function getPhases()
    {
        return $this->phases;
    }

    /**
     * @param \string[] $phases
     */
    public function setPhases($phases)
    {
        if (!is_array($phases)) {
            $phases = [$phases];
        }

        $this->phases = ArrayAccess::filterByWhitelist($phases, self::$phaseList);
    }

    /**
     * @return string
     */
    public function getDateType()
    {
        return $this->dateType;
    }

    /**
     * @param string $dateType
     *
     * @throws InvalidPricingConfigException
     */
    public function setDateType($dateType)
    {
        if (!in_array($dateType, self::$dateTypeList, true)) {
            throw new InvalidPricingConfigException("LunarPhase Date Type is invalid");
        }

        $this->dateType = $dateType;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return array_replace(parent::__toArray(), [
            'phases'   => $this->getPhases(),
            'dateType' => $this->getDateType()
        ]);
    }

}