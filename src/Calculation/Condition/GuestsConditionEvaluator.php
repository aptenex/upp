<?php

namespace Aptenex\Upp\Calculation\Condition;

use Aptenex\Upp\Calculation\ControlItem\ControlItemInterface;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Parser\Structure\Condition\GuestsCondition;

class GuestsConditionEvaluator implements ConditionEvaluationInterface
{

    /**
     * @param PricingContext $context
     * @param Condition $condition
     * @param ControlItemInterface $controlItem
     * @return null
     */
    public function evaluate(PricingContext $context, Condition $condition, ControlItemInterface $controlItem)
    {
        /** @var GuestsCondition $config */
        $config = $condition->getConditionConfig();

        /*
         * Working off the policy of matched=true until proven otherwise (simpler logic - I think)
         */

        $matched = true;

        $min = (int) $config->getMinimum();
        $max = (int) $config->getMaximum();

        if ($min !== 0 && $context->getGuests() < $config->getMinimum()) {
            $matched = false;
        }

        if ($max !== 0 && $context->getGuests() > $config->getMaximum()) {
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