<?php

namespace Aptenex\Upp\Calculation;

use Aptenex\Upp\Parser\Structure\Defaults;
use Aptenex\Upp\Parser\Structure\Period;

class MinimumNightsCalculator
{

    public function calculateMinimumNights(Defaults $defaults, \Aptenex\Upp\Calculation\ControlItem\Period $period): ?int
    {
        $minimumNightPotentialValues = [];

        if ($defaults->hasMinimumNights()) {
            $minimumNightPotentialValues[] = (int)$defaults->getMinimumNights();
        }

        /** @var Period $config */
        $config = $period->getControlItemConfig();

        if ($config->hasMinimumNights()) {
            $minimumNightPotentialValues[] = (int)$config->getMinimumNights();
        }

        $dayOfWeekConfig = $period->getDayOfWeekConfigForStartDate();

        if ($dayOfWeekConfig !== null && $dayOfWeekConfig->hasMinimumNights()) {
            $minimumNightPotentialValues[] = (int) $dayOfWeekConfig->getMinimumNights();
        }

        return \array_pop($minimumNightPotentialValues);
    }


}