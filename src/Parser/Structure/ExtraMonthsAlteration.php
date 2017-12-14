<?php

namespace Aptenex\Upp\Parser\Structure;

class ExtraMonthsAlteration
{

    /**
     * @var bool
     */
    private $applyToTotal = false;

    /**
     * @var string
     */
    private $calculationMethod = Rate::METHOD_FIXED;

    /**
     * @var string
     */
    private $calculationOperand = Operand::OP_EQUALS;

    /**
     * @var bool
     */
    private $makePreviousMonthsSameRate = true;

    /**
     * @var number
     */
    private $extraNightsRate;

    /**
     * @var number
     */
    private $numberOfMonthsDeposit;

    /**
     * @var array
     */
    private $brackets = [];

    /**
     * @return boolean
     */
    public function isApplyToTotal()
    {
        return $this->applyToTotal;
    }

    /**
     * @param boolean $applyToTotal
     */
    public function setApplyToTotal($applyToTotal)
    {
        $this->applyToTotal = $applyToTotal;
    }

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
     * @return bool
     */
    public function isMakePreviousMonthsSameRate(): bool
    {
        return $this->makePreviousMonthsSameRate;
    }

    /**
     * @param bool $makePreviousMonthsSameRate
     */
    public function setMakePreviousMonthsSameRate(bool $makePreviousMonthsSameRate)
    {
        $this->makePreviousMonthsSameRate = $makePreviousMonthsSameRate;
    }

    /**
     * @return number
     */
    public function getExtraNightsRate()
    {
        return $this->extraNightsRate;
    }

    /**
     * @param number $extraNightsRate
     */
    public function setExtraNightsRate($extraNightsRate)
    {
        $this->extraNightsRate = $extraNightsRate;
    }

    /**
     * @return number
     */
    public function getNumberOfMonthsDeposit()
    {
        return $this->numberOfMonthsDeposit;
    }

    /**
     * @param number $numberOfMonthsDeposit
     */
    public function setNumberOfMonthsDeposit($numberOfMonthsDeposit)
    {
        $this->numberOfMonthsDeposit = $numberOfMonthsDeposit;
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
     * @return array
     */
    public function __toArray()
    {
        return [
            'calculationMethod'          => $this->getCalculationMethod(),
            'calculationOperand'         => $this->getCalculationOperand(),
            'applyToTotal'               => $this->isApplyToTotal(),
            'makePreviousMonthsSameRate' => $this->isMakePreviousMonthsSameRate(),
            'extraNightsRate'            => $this->getExtraNightsRate(),
            'numberOfMonthsDeposit'      => $this->getNumberOfMonthsDeposit(),
            'brackets'                   => $this->getBrackets()
        ];
    }

}