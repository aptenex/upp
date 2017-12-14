<?php

namespace Aptenex\Upp\Calculation\ControlItem;

use Aptenex\Upp\Calculation\Condition\ConditionCollection;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Calculation\Pricing\Rate;

interface ControlItemInterface
{

    /**
     * @param FinalPrice $finalPrice
     */
    public function __construct(FinalPrice $finalPrice);

    /**
     * @return bool
     */
    public function containsArrivalDayInMatchedNights();

    /**
     * @return bool
     */
    public function containsDepartureDayInMatchedNights();

    /**
     * @return boolean
     */
    public function isMatched();

    /**
     * @param boolean $matched
     */
    public function setMatched($matched);

    /**
     * @return boolean
     */
    public function isGlobal();

    /**
     * @param boolean $global
     */
    public function setGlobal($global);

    /**
     * @return int
     */
    public function getCalculatedNoNights();

    /**
     * @return \Aptenex\Upp\Calculation\Night[]
     */
    public function getMatchedNights();

    /**
     * @param \Aptenex\Upp\Calculation\Night[] $matchedDays
     */
    public function setMatchedNights($matchedDays);

    /**
     * @return \Aptenex\Upp\Parser\Structure\Period
     */
    public function getControlItemConfig();

    /**
     * @param \Aptenex\Upp\Parser\Structure\Period $periodConfig
     */
    public function setControlItemConfig($periodConfig);

    /**
     * @return string[]
     */
    public function getFailuresIfMatched();

    /**
     * @param string[] $failuresIfMatched
     */
    public function setFailuresIfMatched($failuresIfMatched);

    /**
     * @param string $failureMessage
     */
    public function addFailureIfMatched($failureMessage);

    /**
     * @return ConditionCollection
     */
    public function getConditions();
    /**
     * @param ConditionCollection $conditions
     */
    public function setConditions($conditions);

    /**
     * @return FinalPrice
     */
    public function getFinalPrice();

    /**
     * @return Rate
     */
    public function getRate();

    /**
     * @return int
     */
    public function getId();

}