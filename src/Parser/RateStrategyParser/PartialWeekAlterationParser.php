<?php

namespace Aptenex\Upp\Parser\RateStrategyParser;

use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\BaseChildParser;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Parser\Structure\Operator;
use Aptenex\Upp\Parser\Structure\PartialWeekAlteration;
use Aptenex\Upp\Parser\Structure\Rate;

class PartialWeekAlterationParser extends BaseChildParser
{

    /**
     * @param array $data
     *
     * @return PartialWeekAlteration
     */
    public function parse($data)
    {
        if (empty($data)) {
            return null; // No strategy set
        }

        $p = new PartialWeekAlteration();

        $p->setMinimumWeekCount(ArrayAccess::get('minimumWeekCount', $data, 0));
        $p->setMaximumWeekCount(ArrayAccess::get('maximumWeekCount', $data, null));
        $p->setCalculationMethod(ArrayAccess::get('calculationMethod', $data, Rate::METHOD_FIXED));

        if (ArrayAccess::has('calculationOperator', $data)) {
            $p->setCalculationOperator(ArrayAccess::get('calculationOperator', $data, Operator::OP_EQUALS));
        } else {
            // Deprecated
            $p->setCalculationOperator(ArrayAccess::get('calculationOperand', $data, Operator::OP_EQUALS));
        }

        $p->setBrackets(ArrayAccess::get('brackets', $data, []));

        if (empty($p->getBrackets())) {
            // This strategy does not apply if there are no brackets
            return null;
        }

        return $p;
    }

}