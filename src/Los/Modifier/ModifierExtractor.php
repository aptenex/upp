<?php

namespace Los\Modifier;

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
     * @param string $mode
     * @param Modifier $modifier
     * @return bool
     */
    public function isModifierSupportedByMode(string $mode, Modifier $modifier): bool
    {
        if ($mode === PricingContext::MODE_LOS_EXCLUDE_MANDATORY_FEES_AND_TAXES) {
            if ($modifier->isHidden()) {
                return true; // Any hidden ones are supported
            }

            if (empty($modifier->getConditions())) {
                return false; // Mandatory, non-hidden modifiers are NOT supported
            }
        }

        return true;
    }

    /**
     * @param string $mode
     * @param Modifier[] $modifiers
     * @return Modifier[]
     */
    public function extractSupportedModifiers(string $mode, array $modifiers): array
    {
        $items = [];

        foreach($modifiers as $modifier) {
            if ($this->isModifierSupportedByMode($mode, $modifier)) {
                $items[] = $modifier;
            }
        }

        return $items;
    }

    /**
     * @param string $mode
     * @param Modifier[] $modifiers
     * @return Modifier[]
     */
    public function extractUnsupportedModifiers(string $mode, array $modifiers): array
    {
        $items = [];

        foreach($modifiers as $modifier) {
            if (!$this->isModifierSupportedByMode($mode, $modifier)) {
                $items[] = $modifier;
            }
        }

        return $items;
    }

}