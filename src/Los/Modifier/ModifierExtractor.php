<?php

namespace Aptenex\Upp\Los\Modifier;

use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Parser\Structure\Modifier;

/**
 * Class ModifierExtractor
 *
 * This class is used to extract and determine whether certain modifiers are supported in various LOS Modes.
 *
 * If the mode is exclude fees and taxes then remove them from LOS generation, and be able to extract them
 * to push them separately in the ARI.
 *
 * @package Los\Modifier
 */
class ModifierExtractor
{

    /**
     * @param PricingContext $context
     * @param Modifier $modifier
     *
     * @return bool
     */
    public function isModifierSupportedByMode(PricingContext $context, Modifier $modifier): bool
    {
        if ($context->hasCalculationMode(PricingContext::CALCULATION_MODE_LOS_EXCLUDE_MANDATORY_FEES_AND_TAXES)) {
            if ($modifier->isHidden()) {
                return true; // Any hidden ones are supported
            }

            if ($modifier->getRate()->getCalculationOperand() === 'subtraction') {
                return true; // Discounts always supported since it will be part of base price
            }

            if (empty($modifier->getConditions())) {
                return false; // Mandatory, non-hidden modifiers are NOT supported
            }
        }

        return true;
    }

    /**
     * @param PricingContext $context
     * @param Modifier[] $modifiers
     * @return Modifier[]
     */
    public function extractSupportedModifiers(PricingContext $context, array $modifiers): array
    {
        $items = [];

        foreach($modifiers as $modifier) {
            if ($this->isModifierSupportedByMode($context, $modifier)) {
                $items[] = $modifier;
            }
        }

        return $items;
    }

    /**
     * @param PricingContext $context
     * @param Modifier[] $modifiers
     * @return Modifier[]
     */
    public function extractUnsupportedModifiers(PricingContext $context, array $modifiers): array
    {
        $items = [];

        foreach($modifiers as $modifier) {
            if (!$this->isModifierSupportedByMode($context, $modifier)) {
                $items[] = $modifier;
            }
        }

        return $items;
    }

}