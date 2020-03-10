<?php

namespace Aptenex\Upp\Calculation\Pricing;

use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Parser\Structure\Modifier;

class ModifierRateCalculator
{

    /**
     * @param PricingContext $context
     * @param FinalPrice $fp
     * @param array $calculationOrders
     */
    public function compute(PricingContext $context, FinalPrice $fp, array $calculationOrders = []): void
    {
        foreach($fp->getStay()->getModifiersUsed() as $modifier) {

            /** @var Modifier $modifierConfig */
            $modifierConfig = $modifier->getControlItemConfig();

            if (
                !empty($calculationOrders) &&
                !\in_array($modifierConfig->getCalculationOrderFromType(), $calculationOrders, true)
            ) {
                continue;
            }

            $cuc = new RatePerConditionalUnitCalculator();
            $cuc->applyConditionalRateModifications($fp, $modifier);
        }
    }

}