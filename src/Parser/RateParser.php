<?php

namespace Aptenex\Upp\Parser;

use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\Structure\Rate;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Exception\InvalidPricingConfigException;

class RateParser
{

    /**
     * @param array $data
     * @return Rate
     */
    public function parse(array $data)
    {
        $r = new Rate();

        $r->setType(ArrayAccess::getOrException('type', $data, InvalidPricingConfigException::class, 'Rate type must be specified'));
        $r->setAmount(ArrayAccess::get('amount', $data, 0));
        $r->setDamageDeposit(ArrayAccess::get('damageDeposit', $data, 0));
        $r->setBasePriceOnly(ArrayAccess::get('basePriceOnly', $data, false));
        $r->setCalculationMethod(ArrayAccess::get('calculationMethod', $data, Rate::METHOD_FIXED));
        $r->setCalculationOperand(ArrayAccess::get('calculationOperand', $data, Operand::OP_ADDITION));

        $r->setTaxable(ArrayAccess::get('taxable', $data, false));
        $r->setApplicableTaxes(ArrayAccess::get('applicableTaxes', $data, []));

        $r->setStrategy((new RateStrategyParser())->parse(ArrayAccess::get('strategy', $data, null)));

        return $r;
    }

}