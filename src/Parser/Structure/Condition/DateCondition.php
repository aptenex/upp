<?php

namespace Aptenex\Upp\Parser\Structure\Condition;

use Aptenex\Upp\Parser\Structure\Condition;

class DateCondition extends Condition
{

    /**
     * @var string
     */
    private $startDate;

    /**
     * @var string
     */
    private $endDate;

    /**
     * Empty means any days
     *
     * @var array
     */
    private $arrivalDays = [];

    /**
     * Empty means any days
     *
     * @var array
     */
    private $departureDays = [];

    /**
     * @return string
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param string $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return string
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param string $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return array
     */
    public function getArrivalDays()
    {
        return $this->arrivalDays;
    }

    /**
     * @param array $arrivalDays
     */
    public function setArrivalDays($arrivalDays)
    {
        if (is_string($arrivalDays)) {
            $arrivalDays = [$arrivalDays];
        }

        $this->arrivalDays = $arrivalDays;
    }

    /**
     * @return array
     */
    public function getDepartureDays()
    {
        return $this->departureDays;
    }

    /**
     * @param array $departureDays
     */
    public function setDepartureDays($departureDays)
    {
        if (is_string($departureDays)) {
            $departureDays = [$departureDays];
        }

        $this->departureDays = $departureDays;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return array_replace(parent::__toArray(), [
            'startDate'     => $this->getStartDate(),
            'endDate'       => $this->getEndDate(),
            'arrivalDays'   => $this->getArrivalDays(),
            'departureDays' => $this->getDepartureDays()
        ]);
    }

}