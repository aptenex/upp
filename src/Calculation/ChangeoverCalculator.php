<?php

namespace Aptenex\Upp\Calculation;

class ChangeoverCalculator
{

    public function getArrivalDays(\Aptenex\Upp\Calculation\ControlItem\ControlItemInterface $controlItem): array
    {
        $ciConfig = $controlItem->getControlItemConfig();

        if ($ciConfig->getRate()->hasDaysOfWeek()) {
            return $ciConfig->getRate()->getDaysOfWeek()->getArrivalChangeoverList();
        }

        $dateCon = $ciConfig->getDateCondition();

        if ($dateCon !== null) {
            return $dateCon->getArrivalDays();
        }

        return [];
    }

    public function getDepartureDays(\Aptenex\Upp\Calculation\ControlItem\ControlItemInterface $controlItem): array
    {
        $ciConfig = $controlItem->getControlItemConfig();

        if ($ciConfig->getRate()->hasDaysOfWeek()) {
            return $ciConfig->getRate()->getDaysOfWeek()->getDepartureChangeoverList();
        }

        $dateCon = $ciConfig->getDateCondition();

        if ($dateCon !== null) {
            return $dateCon->getDepartureDays();
        }

        return [];
    }

}