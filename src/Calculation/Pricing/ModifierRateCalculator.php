<?php

namespace Aptenex\Upp\Calculation\Pricing;

use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Calculation\FinalPrice;

class ModifierRateCalculator
{

    /**
     * @param PricingContext $context
     * @param FinalPrice $fp
     */
    public function compute(PricingContext $context, FinalPrice $fp)
    {
        foreach($fp->getStay()->getModifiersUsed() as $modifier) {
            $cuc = new RatePerConditionalUnitCalculator();
            $cuc->applyConditionalRateModifications($fp, $modifier);
        }
    }

}