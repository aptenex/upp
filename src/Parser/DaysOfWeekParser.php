<?php

namespace Aptenex\Upp\Parser;

use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\Structure\DaysOfWeek\DayConfig;
use Aptenex\Upp\Parser\Structure\DaysOfWeek\DaysOfWeek;
use Aptenex\Upp\Parser\Structure\Operator;
use Aptenex\Upp\Parser\Structure\Rate;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Exception\InvalidPricingConfigException;

class DaysOfWeekParser extends BaseChildParser
{

    /**
     * @param array $data
     *
     * @return DaysOfWeek
     */
    public function parse(array $data)
    {
        $d = new DaysOfWeek();

        $d->setCalculationMethod(ArrayAccess::get('calculationMethod', $data, Rate::METHOD_FIXED));

        $days = [];

        if (isset($data['days']) && !empty($data['days'])) {
            foreach($data['days'] as $day => $dayConfig) {
                $dc = new DayConfig();

                try {
                    $dc->setDay($day);

                    if (ArrayAccess::has('day', $dayConfig)) {
                        $dc->setDay(ArrayAccess::get('day', $dayConfig));
                    }

                    $dc->setChangeover(ArrayAccess::get('changeover', $dayConfig));
                } catch (InvalidPricingConfigException $e) {
                    continue; // Skip adding
                }

                $dc->setAmount(ArrayAccess::get('amount', $dayConfig));
                $dc->setMinimumNights(ArrayAccess::get('minimumNights', $dayConfig));

                $days[$dc->getDay()] = $dc;
            }
        }

        $d->setDays($days);

        return $d;
    }

}