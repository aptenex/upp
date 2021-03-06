<?php

namespace Aptenex\Upp\Parser\Structure;

interface PeriodStrategy
{

    /**
     * @return array
     */
    public function getBrackets();

    /**
     * @param array $brackets
     * @return mixed
     */
    public function setBrackets($brackets);

    /**
     * @return string
     */
    public function getCalculationMethod();

    /**
     * @param string $calculationMethod
     */
    public function setCalculationMethod($calculationMethod);

    /**
     * @return string
     */
    public function getCalculationOperator();

    /**
     * @param string $calculationOperand
     */
    public function setCalculationOperator($calculationOperand);

    /**
     * @return array
     */
    public function __toArray();

}