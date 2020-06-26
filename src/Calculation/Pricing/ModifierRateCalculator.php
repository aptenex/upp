<?php

namespace Aptenex\Upp\Calculation\Pricing;

use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Parser\Structure\Modifier;
use function in_array;

class ModifierRateCalculator
{

    /**
     * @param PricingContext $context
     * @param FinalPrice $fp
     * @param array $calculationOrders
     * @param array $priceGroups
     */
    public function compute(PricingContext $context, FinalPrice $fp, array $calculationOrders = [], array $priceGroups = []): void
    {
        foreach($fp->getStay()->getModifiersUsed() as $modifier) {

            /** @var Modifier $modConfig */
            $modConfig = $modifier->getControlItemConfig();

            if (!empty($priceGroups) && !in_array($modConfig->getPriceGroup(), $priceGroups, true)) {
                continue;
            }

            /** @var Modifier $modifierConfig */
            $modifierConfig = $modifier->getControlItemConfig();

            if (
                !empty($calculationOrders) &&
                !in_array($modifierConfig->getCalculationOrderFromType(), $calculationOrders, true)
            ) {
                continue;
            }

            $cuc = new RatePerConditionalUnitCalculator();
            $cuc->applyConditionalRateModifications($fp, $modifier);
        }
    }

}