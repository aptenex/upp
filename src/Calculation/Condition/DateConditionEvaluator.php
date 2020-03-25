<?php

namespace Aptenex\Upp\Calculation\Condition;

use Aptenex\Upp\Calculation\ChangeoverCalculator;
use Aptenex\Upp\Exception\Error;
use Aptenex\Upp\Exception\ErrorHandler;
use Aptenex\Upp\Helper\DateTools;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Helper\LanguageTools;
use Aptenex\Upp\Parser\Structure\Condition\DateCondition;
use Aptenex\Upp\Calculation\ControlItem\ControlItemInterface;
use Aptenex\Upp\Exception\CannotMatchRequestedDatesException;

class DateConditionEvaluator extends BaseEvaluator implements ConditionEvaluationInterface
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
        /** @var DateCondition $config */
        $config = $condition->getConditionConfig();

        $startDate = new \DateTime($config->getStartDate());
        $endDate = new \DateTime($config->getEndDate());

        $matchedDays = [];
        $range = DateTools::getNightsFromRange($context->getArrivalDateObj(), $context->getDepartureDateObj());

        foreach($range as $date) {
            if ($date >= $startDate && $date <= $endDate) {
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
        $errorHandler = $controlItem->getFinalPrice()->getErrors();

        $changeoverCalculator = new ChangeoverCalculator();

        $arrivalDaysList = $changeoverCalculator->getArrivalDays($controlItem);
        $departureDaysList = $changeoverCalculator->getDepartureDays($controlItem);

        if (!empty($arrivalDaysList) && $controlItem->containsArrivalDayInMatchedNights()) {
            $arrivalDay = strtolower($context->getArrivalDateObj()->format('l'));

            if (!in_array($arrivalDay, $arrivalDaysList, true)) {
                $controlItem->addFailureIfMatched(LanguageTools::trans('REQUIRED_ARRIVAL_DAY', [
                    '%list%' => LanguageTools::humanReadableList(LanguageTools::translateDaysOfWeek($arrivalDaysList)),
                ]));

                $errorHandler->addErrorFromRaw(
                    Error::TYPE_START_DAY_MISMATCH,
                    LanguageTools::humanReadableList($arrivalDaysList),
                    LanguageTools::humanReadableList(LanguageTools::translateDaysOfWeek($arrivalDaysList))
                );
            }
        }

        if (!empty($departureDaysList) && $controlItem->containsDepartureDayInMatchedNights()) {
            $departureDay = strtolower($context->getDepartureDateObj()->format('l'));

            if (!in_array($departureDay, $departureDaysList, true)) {
                $controlItem->addFailureIfMatched(LanguageTools::trans('REQUIRED_DEPARTURE_DAY', [
                    '%list%' => LanguageTools::humanReadableList(LanguageTools::translateDaysOfWeek($departureDaysList)),
                ]));

                $errorHandler->addErrorFromRaw(
                    Error::TYPE_END_DAY_MISMATCH,
                    LanguageTools::humanReadableList($departureDaysList),
                    LanguageTools::humanReadableList(LanguageTools::translateDaysOfWeek($departureDaysList))
                );
            }
        }
    }

}