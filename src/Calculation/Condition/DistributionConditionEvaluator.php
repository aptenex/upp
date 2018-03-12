<?php

namespace Aptenex\Upp\Calculation\Condition;

use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Calculation\ControlItem\ControlItemInterface;
use Aptenex\Upp\Parser\Structure\Condition\DistributionCondition;

class DistributionConditionEvaluator implements ConditionEvaluationInterface
{

    /**
     * @param PricingContext $context
     * @param Condition $condition
     * @param ControlItemInterface $controlItem
     *
     * @return null
     */
    public function evaluate(PricingContext $context, Condition $condition, ControlItemInterface $controlItem)
    {
        /** @var DistributionCondition $config */
        $config = $condition->getConditionConfig();

        $channels = $config->getChannels();

        if (empty($context->getDistributionChannel())) {
            $condition->setMatched(false);
        } else {
            $condition->setMatched(in_array($context->getDistributionChannel(), $channels, true));

            if ($condition->getConditionConfig()->isInverse()) {
                $condition->setMatched(!$condition->isMatched());
            }
        }
    }

    public function postEvaluateAfterMatched(PricingContext $context, Condition $condition, ControlItemInterface $controlItem)
    {

    }

}