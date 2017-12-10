<?php

namespace Aptenex\Upp\Parser\RateStrategyParser;

use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Parser\Structure\PartialWeekAlteration;
use Aptenex\Upp\Parser\Structure\Rate;

class PartialWeekAlterationParser
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
        $p->setCalculationOperand(ArrayAccess::get('calculationOperand', $data, Operand::OP_EQUALS));
        $p->setBrackets(ArrayAccess::get('brackets', $data, []));

        return $p;
    }

}