<?php

namespace Aptenex\Upp\Calculation\ControlItem;

use Aptenex\Upp\Calculation\Night;

class Period extends AbstractControlItem
{

    /**
     * @return \Aptenex\Upp\Calculation\Night|null
     */
    public function getFirstMatchedNight(): ?Night
    {
        return $this->getMatchedNights()[0] ?? null;
    }

}