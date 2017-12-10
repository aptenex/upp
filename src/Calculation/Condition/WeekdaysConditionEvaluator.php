<?php

namespace Aptenex\Upp\Calculation\Condition;

use Aptenex\Upp\Calculation\ControlItem\ControlItemInterface;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Helper\DateTools;
use Aptenex\Upp\Parser\Structure\Condition\WeekdaysCondition;

class WeekdaysConditionEvaluator implements ConditionEvaluationInterface
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
        /** @var WeekdaysCondition $config */
        $config = $condition->getConditionConfig();

        $weekdays = $config->getWeekdays();

        $matchedDays = [];

        $dateRange = DateTools::getNightsFromRange($context->getArrivalDateObj(), $context->getDepartureDateObj());

        foreach($dateRange as $date) {
            $weekday = strtolower($date->format("l"));
            if (in_array($weekday, $weekdays)) {
                $matchedDays[] = new MatchedDate($date, $controlItem, $condition);
            }
        }

        $condition->setDatesMatched($matchedDays);

        if (count($matchedDays) > 0) {
            $condition->setMatched(true);
        }

        if ($condition->getConditionConfig()->isInverse()) {
            $condition->setMatched(!$condition->isMatched());
        }
    }

    public function postEvaluateAfterMatched(PricingContext $context, Condition $condition, ControlItemInterface $controlItem)
    {

    }

}