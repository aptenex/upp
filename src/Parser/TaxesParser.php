<?php

namespace Aptenex\Upp\Parser;

use Aptenex\Upp\Exception\InvalidPricingConfigException;
use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\Structure\Rate;
use Aptenex\Upp\Parser\Structure\Tax;

class TaxesParser extends BaseChildParser
{

    /**
     * @param array $taxesArray
     * @return Tax[]
     */
    public function parse(array $taxesArray)
    {
        $t = [];

        foreach($taxesArray as $index => $tax) {
            $t[] = $this->parseTax($tax, $index);
        }

        return $t;
    }

    private function parseTax($taxData, $index)
    {
        $t = new Tax();

        $t->setName(ArrayAccess::getOrException(
            'name',
            $taxData,
            InvalidPricingConfigException::class,
            sprintf("The 'name' parameter is not set for the tax at index %s", $index)
        ));

        $t->setUuid(ArrayAccess::get(
            'uuid',
            $taxData,
            null
        ));

        $t->setAmount(ArrayAccess::getOrException(
            'amount',
            $taxData,
            InvalidPricingConfigException::class,
            sprintf("The 'amount' parameter is not set for the tax at index %s", $index)
        ));

        $t->setType(ArrayAccess::get('type', $taxData, Tax::TYPE_TAX));
        $t->setDescription(ArrayAccess::get('description', $taxData));

        $t->setCalculationMethod(ArrayAccess::get('calculationMethod', $taxData, Rate::METHOD_PERCENTAGE));

        $t->setIncludeBasePrice(ArrayAccess::get('includeBasePrice', $taxData, true));

        $t->setIncludeExtras(ArrayAccess::get('includeExtras', $taxData, true));
        $t->setExtrasWhitelist(ArrayAccess::get('extrasWhitelist', $taxData, []));

        return $t;
    }

}