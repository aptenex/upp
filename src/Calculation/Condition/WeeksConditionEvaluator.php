<?php

namespace Aptenex\Upp\Calculation\Condition;

use Aptenex\Upp\Calculation\ControlItem\ControlItemInterface;
use Aptenex\Upp\Calculation\ControlItem\Modifier;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Parser\Structure\Condition\NightsCondition;
use Aptenex\Upp\Parser\Structure\Condition\WeeksCondition;

class WeeksConditionEvaluator implements ConditionEvaluationInterface
{

    /**
     * @param PricingContext $context
     * @param Condition $condition
     * @param ControlItemInterface $controlItem
     * @return null
     */
    public function evaluate(PricingContext $context, Condition $condition, ControlItemInterface $controlItem)
    {
        /** @var WeeksCondition $config */
        $config = $condition->getConditionConfig();

        /*
         * Working off the policy of matched=true until proven otherwise (simpler logic - I think)
         */

        $matched = true;

        $noMatched = count($controlItem->getMatchedNights());

        if ($controlItem instanceof Modifier) {
            $noMatched = $controlItem->getCalculatedNoNights();
        }

        // Now we've got the nights we need to convert this into weeks
        $noMatched = floor($noMatched / 7);

        $min = (int) $config->getMinimum();
        $max = (int) $config->getMaximum();

        if ($min !== 0 && $noMatched < $config->getMinimum()) {
            $matched = false;
        }

        if ($max !== 0 && $noMatched > $config->getMaximum()) {
            $matched = false;
        }

        $condition->setMatched($matched);

        if ($condition->getConditionConfig()->isInverse()) {
            $condition->setMatched(!$condition->isMatched());
        }
    }

    public function postEvaluateAfterMatched(PricingContext $context, Condition $condition, ControlItemInterface $controlItem)
    {

    }

}