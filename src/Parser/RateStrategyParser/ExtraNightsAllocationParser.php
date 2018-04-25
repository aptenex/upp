<?php

namespace Aptenex\Upp\Parser\RateStrategyParser;

use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\Structure\ExtraNightsAlteration;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Parser\Structure\Rate;

class ExtraNightsAllocationParser
{

    /**
     * @param array $data
     *
     * @return ExtraNightsAlteration
     */
    public function parse($data)
    {
        if (empty($data)) {
            return null; // No strategy set
        }

        $p = new ExtraNightsAlteration();

        $p->setApplyToTotal(ArrayAccess::get('applyToTotal', $data, false));
        $p->setMakePreviousNightsSameRate(ArrayAccess::get('makePreviousNightsSameRate', $data, true));
        $p->setCalculationMethod(ArrayAccess::get('calculationMethod', $data, Rate::METHOD_FIXED));
        $p->setCalculationOperand(ArrayAccess::get('calculationOperand', $data, Operand::OP_EQUALS));
        $p->setBrackets(ArrayAccess::get('brackets', $data, []));

        if (empty($p->getBrackets())) {
            // This strategy does not apply if there are no brackets
            return null;
        }

        return $p;
    }

}