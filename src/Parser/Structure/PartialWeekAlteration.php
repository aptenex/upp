<?php

namespace Aptenex\Upp\Parser\Structure;

class PartialWeekAlteration implements PeriodStrategy
{

    /**
     * @var string
     */
    private $calculationMethod = Rate::METHOD_PERCENTAGE;

    /**
     * @var string
     */
    private $calculationOperand = Operand::OP_EQUALS;

    /**
     * @var int|null
     */
    private $minimumWeekCount = 1;

    /**
     * @var int|null
     */
    private $maximumWeekCount = null; // Unlimited

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
    public function getMinimumWeekCount()
    {
        return $this->minimumWeekCount;
    }

    /**
     * @param int|null $minimumWeekCount
     */
    public function setMinimumWeekCount($minimumWeekCount)
    {
        $this->minimumWeekCount = $minimumWeekCount;
    }

    /**
     * @return int|null
     */
    public function getMaximumWeekCount()
    {
        return $this->maximumWeekCount;
    }

    /**
     * @param int|null $maximumWeekCount
     */
    public function setMaximumWeekCount($maximumWeekCount)
    {
        if ($maximumWeekCount == 0) {
            $maximumWeekCount = null;
        }

        $this->maximumWeekCount = $maximumWeekCount;
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
            'calculationMethod'        => $this->getCalculationMethod(),
            'calculationOperand'       => $this->getCalculationOperand(),
            'minimumWeekCount'         => $this->getMinimumWeekCount(),
            'maximumWeekCount'         => $this->getMaximumWeekCount(),
            'brackets'                 => $this->getBrackets()
        ];
    }

}