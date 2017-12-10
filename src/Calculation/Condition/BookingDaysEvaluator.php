<?php

namespace Aptenex\Upp\Calculation\Condition;

use Aptenex\Upp\Calculation\ControlItem\ControlItemInterface;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Exception\CannotMatchRequestedDatesException;
use Aptenex\Upp\Parser\Structure\Condition\BookingDaysCondition;

class BookingDaysEvaluator implements ConditionEvaluationInterface
{

    /**
     * @param PricingContext $context
     * @param Condition $condition
     * @param ControlItemInterface $controlItem
     *
     * @return null
     *
     * @throws CannotMatchRequestedDatesException
     */
    public function evaluate(PricingContext $context, Condition $condition, ControlItemInterface $controlItem)
    {
        /** @var BookingDaysCondition $config */
        $config = $condition->getConditionConfig();

        $matched = true;

        $daysBeforeArrival = $context->getNoDaysBeforeArrival();

        $min = (int) $config->getMinimum();
        $max = (int) $config->getMaximum();

        if ($min !== 0 && $daysBeforeArrival < $config->getMinimum()) {
            $matched = false;
        }

        if ($max !== 0 && $daysBeforeArrival > $config->getMaximum()) {
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