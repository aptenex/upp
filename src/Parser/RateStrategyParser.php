<?php

namespace Aptenex\Upp\Parser;

use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\Structure\RateStrategy;

class RateStrategyParser extends BaseChildParser
{

    /**
     * @param array $data
     *
     * @return RateStrategy
     */
    public function parse($data)
    {
        if (empty($data) || $data === null) {
            return null; // No strategy set
        }

        $r = new RateStrategy();

        $r->setPartialWeekAlteration((new RateStrategyParser\PartialWeekAlterationParser($this->getConfig()))->parse(ArrayAccess::get('partialWeekAlteration', $data, [])));
        $r->setExtraNightsAlteration((new RateStrategyParser\ExtraNightsAllocationParser($this->getConfig()))->parse(ArrayAccess::get('extraNightsAlteration', $data, [])));
        $r->setExtraMonthsAlteration((new RateStrategyParser\ExtraMonthsAllocationParser($this->getConfig()))->parse(ArrayAccess::get('extraMonthsAlteration', $data, [])));
        $r->setDaysOfWeekAlteration((new RateStrategyParser\DaysOfWeekAlterationParser($this->getConfig()))->parse(ArrayAccess::get('daysOfWeekAlteration', $data, [])));

        return $r;
    }

}