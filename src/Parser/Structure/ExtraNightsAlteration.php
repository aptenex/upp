<?php

namespace Aptenex\Upp\Parser\Structure;

class ExtraNightsAlteration
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
    private $makePreviousNightsSameRate = true;

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
     * @return boolean
     */
    public function isMakePreviousNightsSameRate()
    {
        return $this->makePreviousNightsSameRate;
    }

    /**
     * @param boolean $makePreviousNightsSameRate
     */
    public function setMakePreviousNightsSameRate($makePreviousNightsSameRate)
    {
        $this->makePreviousNightsSameRate = $makePreviousNightsSameRate;
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
            'makePreviousNightsSameRate' => $this->isMakePreviousNightsSameRate(),
            'brackets'                   => $this->getBrackets()
        ];
    }

}