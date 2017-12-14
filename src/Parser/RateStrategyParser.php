<?php

namespace Aptenex\Upp\Parser;

use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\Structure\RateStrategy;

class RateStrategyParser
{

    /**
     * @param array $data
     *
     * @return RateStrategy
     */
    public function parse($data)
    {
        if (empty($data) || is_null($data)) {
            return null; // No strategy set
        }

        $r = new RateStrategy();

        $r->setPartialWeekAlteration((new RateStrategyParser\PartialWeekAlterationParser)->parse(ArrayAccess::get('partialWeekAlteration', $data, [])));
        $r->setExtraNightsAlteration((new RateStrategyParser\ExtraNightsAllocationParser())->parse(ArrayAccess::get('extraNightsAlteration', $data, [])));
        $r->setExtraMonthsAlteration((new RateStrategyParser\ExtraMonthsAllocationParser())->parse(ArrayAccess::get('extraMonthsAlteration', $data, [])));
        $r->setDaysOfWeekAlteration((new RateStrategyParser\DaysOfWeekAlterationParser())->parse(ArrayAccess::get('daysOfWeekAlteration', $data, [])));

        return $r;
    }

}