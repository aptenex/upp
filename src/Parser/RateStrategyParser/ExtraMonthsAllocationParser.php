<?php

namespace Aptenex\Upp\Parser\RateStrategyParser;

use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\BaseChildParser;
use Aptenex\Upp\Parser\Structure\Rate;
use Aptenex\Upp\Parser\Structure\Operator;
use Aptenex\Upp\Parser\Structure\ExtraMonthsAlteration;

class ExtraMonthsAllocationParser extends BaseChildParser
{

    /**
     * @param array $data
     *
     * @return ExtraMonthsAlteration
     */
    public function parse($data)
    {
        if (empty($data)) {
            return null; // No strategy set
        }

        $p = new ExtraMonthsAlteration();

        $p->setApplyToTotal(ArrayAccess::get('applyToTotal', $data, false));
        $p->setMakePreviousMonthsSameRate(ArrayAccess::get('makePreviousMonthsSameRate', $data, true));
        $p->setCalculationMethod(ArrayAccess::get('calculationMethod', $data, Rate::METHOD_FIXED));

        if (ArrayAccess::has('calculationOperator', $data)) {
            $p->setCalculationOperator(ArrayAccess::get('calculationOperator', $data, Operator::OP_EQUALS));
        } else {
            // Deprecated
            $p->setCalculationOperator(ArrayAccess::get('calculationOperand', $data, Operator::OP_EQUALS));
        }

        $p->setExtraNightsRate(ArrayAccess::get('extraNightsRate', $data, null));
        $p->setNumberOfMonthsDeposit(ArrayAccess::get('numberOfMonthsDeposit', $data, null));
        $p->setBrackets(ArrayAccess::get('brackets', $data, []));

        if (empty($p->getBrackets())) {
            // This strategy does not apply if there are no brackets
            return null;
        }

        return $p;
    }

}