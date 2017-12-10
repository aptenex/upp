<?php

namespace Aptenex\Upp\Calculation\Condition;

class Condition
{

    /**
     * @var bool
     */
    private $matched = false;

    /**
     * @var MatchedDate[]
     */
    private $datesMatched = [];

    /**
     * @var \Aptenex\Upp\Parser\Structure\Condition
     */
    private $conditionConfig;

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
     * @return MatchedDate[]
     */
    public function getDatesMatched()
    {
        return $this->datesMatched;
    }

    /**
     * @param MatchedDate[] $datesMatched
     */
    public function setDatesMatched($datesMatched)
    {
        $this->datesMatched = $datesMatched;
    }

    /**
     * @return \Aptenex\Upp\Parser\Structure\Condition
     */
    public function getConditionConfig()
    {
        return $this->conditionConfig;
    }

    /**
     * @param \Aptenex\Upp\Parser\Structure\Condition $conditionConfig
     */
    public function setConditionConfig($conditionConfig)
    {
        $this->conditionConfig = $conditionConfig;
    }

}