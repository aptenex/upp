<?php

namespace Aptenex\Upp\Calculation;

use Aptenex\Upp\Parser\Structure\ControlItemInterface;
use Aptenex\Upp\Parser\Structure\Period;

class ChangeoverCalculator
{

    public function getArrivalDays(ControlItemInterface $controlItem): array
    {
        /** @var Period $controlItem */

        if ($controlItem->getRate()->hasDaysOfWeek()) {
            return $controlItem->getRate()->getDaysOfWeek()->getArrivalChangeoverList();
        }

        $dateCon = $controlItem->getDateCondition();

        if ($dateCon !== null) {
            return $dateCon->getArrivalDays();
        }

        return [];
    }

    public function getDepartureDays(ControlItemInterface $controlItem): array
    {
        /** @var Period $controlItem */

        if ($controlItem->getRate()->hasDaysOfWeek()) {
            return $controlItem->getRate()->getDaysOfWeek()->getDepartureChangeoverList();
        }

        $dateCon = $controlItem->getDateCondition();

        if ($dateCon !== null) {
            return $dateCon->getDepartureDays();
        }

        return [];
    }

}