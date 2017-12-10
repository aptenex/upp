<?php

namespace Aptenex\Upp\Calculation\ControlItem;

use Aptenex\Upp\Calculation\Condition\ConditionCollection;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Calculation\Night;

class AbstractControlItem implements ControlItemInterface
{

    /**
     * @var int
     */
    protected $id;

    /**
     * @var bool
     */
    protected $matched = false;

    /**
     * @var bool
     */
    protected $global = false;

    /**
     * @var Night[]
     */
    protected $matchedNights = [];

    /**
     * @var ConditionCollection
     */
    protected $conditions;

    /**
     * @var FinalPrice
     */
    protected $finalPrice;

    /**
     * @var \Aptenex\Upp\Parser\Structure\ControlItemInterface
     */
    protected $controlItemConfig;

    /**
     * @var string[]
     */
    protected $failuresIfMatched = [];

    /**
     * @var int
     */
    protected static $idCounter = 0;

    /**
     * @param FinalPrice $finalPrice
     */
    public function __construct(FinalPrice $finalPrice)
    {
        self::$idCounter++;

        $this->id = self::$idCounter;
        $this->finalPrice = $finalPrice;
    }

    /**
     * @return boolean
     */
    public function isMatched()
    {
        return $this->matched;
    }

    /**
     * @param boolean $matched
     */
    public function setMatched($matched)
    {
        $this->matched = $matched;
    }

    /**
     * @return boolean
     */
    public function isGlobal()
    {
        return $this->global;
    }

    /**
     * @param boolean $global
     */
    public function setGlobal($global)
    {
        $this->global = $global;
    }

    /**
     * This will return the total number of nights if this control item does not have a date based condition,
     * if this control item does it will return the number of matched nights
     *
     * @return int
     */
    public function getCalculatedNoNights()
    {
        if ($this->conditions->hasDateBasedCondition()) {
            return count($this->conditions->getDateCondition()->getDatesMatched());
        }

        return $this->finalPrice->getStay()->getNoNights();
    }

    /**
     * @return \Aptenex\Upp\Calculation\Night[]
     */
    public function getMatchedNights()
    {
        return $this->matchedNights;
    }

    /**
     * @param \Aptenex\Upp\Calculation\Night[] $matchedNights
     */
    public function setMatchedNights($matchedNights)
    {
        $this->matchedNights = $matchedNights;
    }

    /**
     * @param Night $night
     */
    public function addMatchedNight(Night $night)
    {
        $this->matchedNights[] = $night;
    }

    /**
     * @return \Aptenex\Upp\Parser\Structure\ControlItemInterface
     */
    public function getControlItemConfig()
    {
        return $this->controlItemConfig;
    }

    /**
     * @param \Aptenex\Upp\Parser\Structure\ControlItemInterface $controlItemConfig
     */
    public function setControlItemConfig($controlItemConfig)
    {
        $this->controlItemConfig = $controlItemConfig;
    }

    /**
     * @return bool
     */
    public function containsArrivalDayInMatchedNights()
    {
        $arrivalDay = $this->getFinalPrice()->getStay()->getArrival();

        // Since matched nights are in order - we only need to look up the first item and check that

        if ($this->getMatchedNights()[0]->getDate()->format("Y-m-d") === $arrivalDay->format("Y-m-d")) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function containsDepartureDayInMatchedNights()
    {
        $departureDay = clone $this->getFinalPrice()->getStay()->getDeparture();

        // We need the second last day since that is always the last matched night
        $lastNightPaidFor = $departureDay->sub(new \DateInterval("P1D"));

        // Since matched nights are in order - we only need to look up the last item and check that

        $lastItem = count($this->getMatchedNights()) - 1;

        if (!array_key_exists($lastItem, $this->getMatchedNights())) {
            return false;
        }

        if ($this->getMatchedNights()[$lastItem]->getDate()->format("Y-m-d") === $lastNightPaidFor->format("Y-m-d")) {
            return true;
        }

        return false;
    }

    /**
     * @return string[]
     */
    public function getFailuresIfMatched()
    {
        return $this->failuresIfMatched;
    }

    /**
     * @param string[] $failuresIfMatched
     */
    public function setFailuresIfMatched($failuresIfMatched)
    {
        $this->failuresIfMatched = $failuresIfMatched;
    }

    /**
     * @param string $failureMessage
     */
    public function addFailureIfMatched($failureMessage)
    {
        $this->failuresIfMatched[] = $failureMessage;
    }

    /**
     * @return ConditionCollection
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param ConditionCollection $conditions
     */
    public function setConditions($conditions)
    {
        $this->conditions = $conditions;
    }

    /**
     * @return FinalPrice
     */
    public function getFinalPrice()
    {
        return $this->finalPrice;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

}