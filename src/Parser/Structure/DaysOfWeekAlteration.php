<?php

namespace Aptenex\Upp\Parser\Structure;

class DaysOfWeekAlteration implements PeriodStrategy
{

    /**
     * @var string
     */
    private $calculationMethod = Rate::METHOD_FIXED;

    /**
     * @var string
     */
    private $calculationOperand = Operand::OP_EQUALS;

    /**
     * If left null, this will be determined by the week amount / 7
     *
     * @var int|null
     */
    private $unmatchedNightAmount = null;

    /**
     * @var bool
     */
    private $allowPartialMatch = true;

    /**
     * @var bool
     */
    private $useWeeklyPriceIfExceeded = false;

    /**
     * @var array
     */
    private $brackets = [];

    /**
     * @return string
     */
    public function getCalculationMethod()
    {
        return $this->calculationMethod;
    }

    /**
     * @param string $calculationMethod
     */
    public function setCalculationMethod($calculationMethod)
    {
        $this->calculationMethod = $calculationMethod;
    }

    /**
     * @return string
     */
    public function getCalculationOperand()
    {
        return $this->calculationOperand;
    }

    /**
     * @param string $calculationOperand
     */
    public function setCalculationOperand($calculationOperand)
    {
        $this->calculationOperand = $calculationOperand;
    }

    /**
     * @return int|null
     */
    public function getUnmatchedNightAmount()
    {
        return $this->unmatchedNightAmount;
    }

    /**
     * @param int|null $unmatchedNightAmount
     */
    public function setUnmatchedNightAmount($unmatchedNightAmount)
    {
        $this->unmatchedNightAmount = $unmatchedNightAmount;
    }

    /**
     * @return bool
     */
    public function hasUnmatchedNightAmount()
    {
        return !is_null($this->unmatchedNightAmount);
    }

    /**
     * @return boolean
     */
    public function isAllowPartialMatch()
    {
        return $this->allowPartialMatch;
    }

    /**
     * @param boolean $allowPartialMatch
     */
    public function setAllowPartialMatch($allowPartialMatch)
    {
        $this->allowPartialMatch = $allowPartialMatch;
    }

    /**
     * @return array
     */
    public function getBrackets()
    {
        return $this->brackets;
    }

    /**
     * @param array $brackets
     */
    public function setBrackets($brackets)
    {
        $this->brackets = $brackets;
    }

    /**
     * @return boolean
     */
    public function isUseWeeklyPriceIfExceeded()
    {
        return $this->useWeeklyPriceIfExceeded;
    }

    /**
     * @param boolean $useWeeklyPriceIfExceeded
     */
    public function setUseWeeklyPriceIfExceeded($useWeeklyPriceIfExceeded)
    {
        $this->useWeeklyPriceIfExceeded = $useWeeklyPriceIfExceeded;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return [
            'calculationMethod'        => $this->getCalculationMethod(),
            'calculationOperand'       => $this->getCalculationOperand(),
            'unmatchedNightAmount'     => $this->getUnmatchedNightAmount(),
            'allowPartialMatch'        => $this->isAllowPartialMatch(),
            'useWeeklyPriceIfExceeded' => $this->isUseWeeklyPriceIfExceeded(),
            'brackets'                 => $this->getBrackets()
        ];
    }

}