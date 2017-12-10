<?php

namespace Aptenex\Upp\Calculation\Pricing;

use Aptenex\Upp\Calculation\FinalPrice;

class ModifierRateCalculator
{

    /**
     * @param FinalPrice $fp
     */
    public function compute(FinalPrice $fp)
    {
        foreach($fp->getStay()->getModifiersUsed() as $modifier) {
            $cuc = new RatePerConditionalUnitCalculator();
            $cuc->applyConditionalRateModifications($fp, $modifier);
        }
    }

}