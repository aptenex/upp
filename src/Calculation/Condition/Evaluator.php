<?php

namespace Aptenex\Upp\Calculation\Condition;

use Aptenex\Upp\Parser\Structure;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Calculation\ControlItem\ControlItemInterface;

class Evaluator
{

    /**
     * @var ConditionEvaluationInterface[]
     */
    private $evaluationMap;

    public function __construct()
    {
        /** @var ConditionEvaluationInterface[] $evaluationMap */
        $this->evaluationMap = [
            Structure\Condition::TYPE_DATE          => new DateConditionEvaluator(),
            Structure\Condition::TYPE_GUESTS        => new GuestsConditionEvaluator(),
            Structure\Condition::TYPE_NIGHTS        => new NightsConditionEvaluator(),
            Structure\Condition::TYPE_WEEKS         => new WeeksConditionEvaluator(),
            Structure\Condition::TYPE_MONTHS        => new MonthsConditionEvaluator(),
            Structure\Condition::TYPE_WEEKDAYS      => new WeekdaysConditionEvaluator(),
            Structure\Condition::TYPE_LUNAR_PHASE   => new LunarPhaseConditionEvaluator(),
            Structure\Condition::TYPE_BOOKING_DAYS  => new BookingDaysEvaluator(),
        ];
    }

    /**
     * @param PricingContext $context
     * @param ControlItemInterface $controlItem
     * @return ConditionCollection
     *
     */
    public function evaluateConditions(PricingContext $context, ControlItemInterface $controlItem)
    {
        $collection = new ConditionCollection($controlItem->getControlItemConfig()->getConditionOperand());
        $controlItem->setConditions($collection);

        $conditions = $controlItem->getControlItemConfig()->getConditions();

        // We need to evaluate the date based conditions first to get the amount of 'matched nights'
        // This is so if a date range exists with say a per week condition, we can fetch the correct
        // amount of nights as the matched nights could be 7 but the total is 14. We need this 7 number.

        // Date Based
        foreach($conditions as $condition) {
            if (in_array($condition->getType(), Structure\Condition::$dateBasedConditions)) {
                $collection->addCondition($this->evaluateCondition($context, $condition, $controlItem, $collection));
            }
        }

        // Unit Based
        foreach ($conditions as $condition) {
            if (in_array($condition->getType(), Structure\Condition::$unitBasedConditions)) {
                $collection->addCondition($this->evaluateCondition($context, $condition, $controlItem, $collection));
            }
        }

        // Now that we have evaluated all conditions we need to determine if the condition
        // set did not include any 'date' based conditions
        if (!$collection->hasDateBasedCondition()) {
            $controlItem->setGlobal(true); // Set global since it cannot be restricted to any matched dates
        }

        return $collection;
    }

    /**
     * @param PricingContext $context
     * @param Structure\Condition $condition
     * @param ControlItemInterface $controlItem
     *
     * @param ConditionCollection $collection
     * @return Condition
     */
    private function evaluateCondition(PricingContext $context, Structure\Condition $condition, ControlItemInterface $controlItem, ConditionCollection $collection)
    {
        $cc = new Condition();

        $cc->setConditionConfig($condition);

        if (array_key_exists($condition->getType(), $this->evaluationMap)) {
            $eval = $this->evaluationMap[$condition->getType()];

            $eval->evaluate($context, $cc, $controlItem);

            if (in_array($cc->getConditionConfig()->getType(), Structure\Condition::$dateBasedConditions, true)) {
                foreach ($cc->getDatesMatched() as $matchedDate) {
                    $collection->addMatchedDate($matchedDate);
                }
            }
        }

        return $cc;
    }

    /**
     * Post conditions do not care about the outcome of the AND/OR, since only conditions that are matched already
     * will be evaluated
     *
     * @param PricingContext $context
     * @param ControlItemInterface $controlItem
     */
    public function evaluatePostConditions(PricingContext $context, ControlItemInterface $controlItem)
    {
        $collection = $controlItem->getConditions();

        foreach($collection->getConditions() as $condition) {
            $config = $condition->getConditionConfig();

            if (!$condition->isMatched()) {
                continue;
            }

            if (!array_key_exists($config->getType(), $this->evaluationMap)) {
                continue;
            }

            $eval = $this->evaluationMap[$config->getType()];
            $eval->postEvaluateAfterMatched($context, $condition, $controlItem);
        }
    }

}