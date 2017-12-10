<?php

namespace Aptenex\Upp\Calculation\Condition;

use Aptenex\Upp\Calculation\ControlItem\ControlItemInterface;
use Aptenex\Upp\Context\PricingContext;

interface ConditionEvaluationInterface
{

    /**
     * @param PricingContext $context
     * @param Condition $condition
     * @param ControlItemInterface $controlItem
     */
    public function evaluate(PricingContext $context, Condition $condition, ControlItemInterface $controlItem);

    /**
     * This will be run to decided if there are any soft - custom failures that need to be addressed such as an arrival day not
     * being valid as it needs to arrive on a saturday.
     *
     * In this post evaluation the KEY point is that all the periods have been evaluated
     *
     * @param PricingContext $context
     * @param Condition $condition
     * @param ControlItemInterface $controlItem
     */
    public function postEvaluateAfterMatched(PricingContext $context, Condition $condition, ControlItemInterface $controlItem);

}