<?php

namespace Aptenex\Upp\Calculation;

use Aptenex\Upp\Parser\Structure\Period;
use Aptenex\Upp\Parser\Structure\Defaults;
use Aptenex\Upp\Parser\Structure\ControlItemInterface;

class MinimumNightsCalculator
{

    public function calculateMinimumNights(Defaults $defaults, ControlItemInterface $period, \DateTime $dateForDayOfWeek): ?int
    {
        /** @var Period $period */

        $minimumNightPotentialValues = [];

        if ($defaults->hasMinimumNights()) {
            $minimumNightPotentialValues[] = (int)$defaults->getMinimumNights();
        }

        if ($period->hasMinimumNights()) {
            $minimumNightPotentialValues[] = (int)$period->getMinimumNights();
        }

        $dayOfWeekConfig = $period->getDayOfWeekConfigForStartDate($dateForDayOfWeek);

        if ($dayOfWeekConfig !== null && $dayOfWeekConfig->hasMinimumNights()) {
            $minimumNightPotentialValues[] = (int) $dayOfWeekConfig->getMinimumNights();
        }

        return \array_pop($minimumNightPotentialValues);
    }

}