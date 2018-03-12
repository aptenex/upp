<?php

namespace Aptenex\Upp\Calculation\Condition;

use Aptenex\Upp\Parser\Structure;

class ConditionCollection
{

    /**
     * @var Condition[]
     */
    private $conditions = [];

    /**
     * @var MatchedDate[]
     */
    private $matchedDates = [];

    /**
     * @var string
     */
    private $operand;

    /**
     * @param string $conditionOperand
     */
    public function __construct($conditionOperand)
    {
        $this->operand = $conditionOperand;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return count($this->conditions) === 0;
    }

    /**
     * @return bool
     */
    public function hasDateBasedCondition()
    {
        foreach($this->getConditions() as $con) {
            if (in_array($con->getConditionConfig()->getType(), Structure\Condition::$dateBasedConditions, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function hasOnlyNonUnitBasedConditions()
    {
        $result = true;

        foreach($this->getConditions() as $con) {
            if (in_array($con->getConditionConfig()->getType(), Structure\Condition::$unitBasedConditions, true)) {
                $result = false; // Single one will set this to false
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function hasDateCondition()
    {
        return $this->getDateCondition() instanceof Condition;
    }

    /**
     * @return Condition|null
     */
    public function getDateCondition()
    {
        foreach($this->getConditions() as $con) {
            if ($con->getConditionConfig()->getType() === Structure\Condition::TYPE_DATE) {
                return $con;
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isValidConditionSet()
    {
        if (count($this->conditions) === 0) {
            return true; // No conditions - always true
        }

        switch ($this->operand) {
            case Structure\Operand::OP_OR:
                return $this->anyConditionsMatched();
            case Structure\Operand::OP_AND:
            default:
                return $this->allConditionsMatched();
        }
    }

    /**
     * @return bool
     */
    public function allConditionsMatched()
    {
        foreach($this->getConditions() as $con) {
            if (!$con->isMatched()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function anyConditionsMatched()
    {
        foreach($this->getConditions() as $con) {
            if ($con->isMatched()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Condition[]
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param Condition[] $conditions
     */
    public function setConditions($conditions)
    {
        $this->conditions = [];

        foreach($conditions as $condition) {
            $this->addCondition($condition);
        }
    }

    /**
     * @param Condition $condition
     */
    public function addCondition(Condition $condition)
    {
        $this->conditions[] = $condition;
    }

    /**
     * @return MatchedDate[]
     */
    public function getMatchedDates()
    {
        return $this->matchedDates;
    }

    /**
     * @param MatchedDate[] $matchedDates
     */
    public function setMatchedDates($matchedDates)
    {
        $this->matchedDates = $matchedDates;
    }

    /**
     * @param MatchedDate $date
     */
    public function addMatchedDate(MatchedDate $date)
    {
        $this->matchedDates[$date->getDate()->format("Y-m-d")] = $date;
    }

    /**
     * @return string
     */
    public function getOperand()
    {
        return $this->operand;
    }

}