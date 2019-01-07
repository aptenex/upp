<?php

namespace Aptenex\Upp\Calculation\Condition;

use Aptenex\Upp\Exception\Error;
use Aptenex\Upp\Helper\DateTools;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Helper\LanguageTools;
use Aptenex\Upp\Parser\Structure\Condition\DateCondition;
use Aptenex\Upp\Calculation\ControlItem\ControlItemInterface;
use Aptenex\Upp\Exception\CannotMatchRequestedDatesException;
use Aptenex\Upp\Parser\Structure\Condition\MultiDateCondition;
use Aptenex\Upp\Util\DateUtils;

class MultiDateConditionEvaluator extends BaseEvaluator implements ConditionEvaluationInterface
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
        /** @var MultiDateCondition $config */
        $config = $condition->getConditionConfig();

        // We need to make a map of all the date ranges in the condition
        $mappedConditionDates = [];

        // Expand the dates, removing any duplicates at the same time, then we can use this
        // to check if any of the dates match
        foreach($config->getDates() as $date) {
            if (!DateUtils::isValidDate($date['start']) || !DateUtils::isValidDate($date['end'])) {
                continue; // Skip invalid dates
            }

            $range = DateUtils::getDateRangeInclusive($date['start'], $date['end']);

            foreach($range as $singleDate) {
                $mappedConditionDates[$singleDate] = $date;
            }
        }

        $matchedDays = [];
        $range = DateTools::getNightsFromRange($context->getArrivalDateObj(), $context->getDepartureDateObj());

        foreach($range as $date) {
            if (isset($mappedConditionDates[$date->format("Y-m-d")])) {
                $matchedDays[] = new MatchedDate($date, $controlItem, $condition);
            }
        }

        // Now, check the strategy and see what we are meant to do with it
        if ($config->getStrategy() === MultiDateCondition::STRATEGY_MATCH) {
            $condition->setDatesMatched($matchedDays);

            if (count($matchedDays) > 0) {
                $condition->setMatched(true);
            }
        } else if ($config->getStrategy() === MultiDateCondition::STRATEGY_NOT_MATCH) {
            /*
             * For the not match strategy, if none of these dates match then it is considered "matched",
             * this is what you'd do if you want this to apply to every season apart from a few
             */
            if (count($matchedDays) > 0) {
                $condition->setMatched(false);
            } else {
                $condition->setMatched(true);
            }
        }

        if ($condition->getConditionConfig()->isInverse()) {
            $condition->setMatched(!$condition->isMatched());
        }
    }

    public function postEvaluateAfterMatched(PricingContext $context, Condition $condition, ControlItemInterface $controlItem)
    {
        /** @var DateCondition $config */
        $config = $condition->getConditionConfig();

        $errorHandler = $controlItem->getFinalPrice()->getErrors();

        if (!empty($config->getArrivalDays()) && $controlItem->containsArrivalDayInMatchedNights()) {
            $arrivalDay = strtolower($context->getArrivalDateObj()->format('l'));

            if (!in_array($arrivalDay, $config->getArrivalDays(), true)) {
                $controlItem->addFailureIfMatched(LanguageTools::trans('REQUIRED_ARRIVAL_DAY', [
                    '%list%' => LanguageTools::humanReadableList(LanguageTools::translateDaysOfWeek($config->getArrivalDays())),
                ]));

                $errorHandler->addErrorFromRaw(
                    Error::TYPE_START_DAY_MISMATCH,
                    LanguageTools::humanReadableList($config->getArrivalDays()),
                    LanguageTools::humanReadableList(LanguageTools::translateDaysOfWeek($config->getArrivalDays()))
                );
            }
        }

        if (!empty($config->getDepartureDays()) && $controlItem->containsDepartureDayInMatchedNights()) {
            $departureDay = strtolower($context->getDepartureDateObj()->format('l'));

            if (!in_array($departureDay, $config->getDepartureDays(), true)) {
                $controlItem->addFailureIfMatched(LanguageTools::trans('REQUIRED_DEPARTURE_DAY', [
                    '%list%' => LanguageTools::humanReadableList(LanguageTools::translateDaysOfWeek($config->getDepartureDays())),
                ]));

                $errorHandler->addErrorFromRaw(
                    Error::TYPE_END_DAY_MISMATCH,
                    LanguageTools::humanReadableList($config->getDepartureDays()),
                    LanguageTools::humanReadableList(LanguageTools::translateDaysOfWeek($config->getDepartureDays()))
                );
            }
        }
    }

}