<?php

namespace Aptenex\Upp\Calculation\Condition;

use Aptenex\Upp\Calculation\ControlItem\ControlItemInterface;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Parser\Structure\Condition\LunarPhaseCondition;
use Solaris\MoonPhase;

class LunarPhaseConditionEvaluator implements ConditionEvaluationInterface
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
        /** @var LunarPhaseCondition $config */
        $config = $condition->getConditionConfig();

        $date = $context->getBookingDateObj()->getTimestamp();

        if ($config->getDateType() === LunarPhaseCondition::ARRIVAL_DATE) {
            $date = $context->getArrivalDateObj()->getTimestamp();
        }

        if ($config->getDateType() === LunarPhaseCondition::DEPARTURE_DATE) {
            $date = $context->getDepartureDateObj()->getTimestamp();
        }

        $moonPhase = new MoonPhase($date);

        $phaseName = strtolower(str_replace(' ', '_', trim($moonPhase->phase_name())));

        if (in_array($phaseName, $config->getPhases(), true)) {
            $condition->setMatched(true); // Matched
        }

        if ($condition->getConditionConfig()->isInverse()) {
            $condition->setMatched(!$condition->isMatched());
        }
    }

    public function postEvaluateAfterMatched(PricingContext $context, Condition $condition, ControlItemInterface $controlItem)
    {

    }

}