<?php

namespace Aptenex\Upp\Parser\Structure;

class ExtraNightsAlteration implements PeriodStrategy
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
    private $calculationOperator = Operator::OP_EQUALS;

    /**
     * @var bool
     */
    private $makePreviousNightsSameRate = true;

    /**
     * @var bool
     */
    private $enablePerGuestPerNight = false;

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
    public function getCalculationOperator()
    {
        return $this->calculationOperator;
    }

    /**
     * @param string $calculationOperator
     */
    public function setCalculationOperator($calculationOperator)
    {
        $this->calculationOperator = $calculationOperator;
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
     * @return bool
     */
    public function isEnablePerGuestPerNight(): bool
    {
        return $this->enablePerGuestPerNight;
    }

    /**
     * @param bool $enablePerGuestPerNight
     */
    public function setEnablePerGuestPerNight($enablePerGuestPerNight): void
    {
        $this->enablePerGuestPerNight = (bool) $enablePerGuestPerNight;
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
            'calculationMethod'             => $this->getCalculationMethod(),
            'calculationOperand'            => $this->getCalculationOperator(),
            'calculationOperator'           => $this->getCalculationOperator(),
            'applyToTotal'                  => $this->isApplyToTotal(),
            'makePreviousNightsSameRate'    => $this->isMakePreviousNightsSameRate(),
            'enablePerGuestPerNight'        => $this->isEnablePerGuestPerNight(),
            'brackets'                      => $this->getBrackets()
        ];
    }

}