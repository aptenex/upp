<?php

namespace Aptenex\Upp\Calculation\ControlItem;

use Aptenex\Upp\Parser\Structure\DaysOfWeek\DayConfig;

class Period extends AbstractControlItem
{

    public function getDayOfWeekConfigForStartDate(): ?DayConfig
    {
        /** @var \Aptenex\Upp\Parser\Structure\Period $config */
        $config = $this->getControlItemConfig();

        if (!$config->getRate()->hasDaysOfWeek()) {
            return null;
        }

        $arrivalDay = $this->getMatchedNights()[0]->getDate();

        $daysOfWeek = $config->getRate()->getDaysOfWeek();

        $dayConfig = $daysOfWeek->getDayConfigByDay(\strtolower($arrivalDay->format('l')));

        if ($dayConfig === null) {
            return null;
        }

        return $dayConfig;
    }

}