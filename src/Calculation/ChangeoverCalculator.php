<?php

namespace Aptenex\Upp\Calculation;

use Aptenex\Upp\Parser\Structure\ControlItemInterface;
use Aptenex\Upp\Parser\Structure\Period;
use Aptenex\Upp\Parser\Structure\Rate;

class ChangeoverCalculator
{

    public function getArrivalDays(ControlItemInterface $controlItem): array
    {
        /** @var Period $controlItem */

        // Only nightly supported for new days of week
        if ($controlItem->getRate()->hasDaysOfWeek() && $controlItem->getRate()->getType() === Rate::TYPE_NIGHTLY) {
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

        // Only nightly supported for new days of week
        if ($controlItem->getRate()->hasDaysOfWeek() && $controlItem->getRate()->getType() === Rate::TYPE_NIGHTLY) {
            return $controlItem->getRate()->getDaysOfWeek()->getDepartureChangeoverList();
        }

        $dateCon = $controlItem->getDateCondition();

        if ($dateCon !== null) {
            return $dateCon->getDepartureDays();
        }

        return [];
    }

}