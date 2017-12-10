<?php

namespace Aptenex\Upp\Parser;

use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\Structure\Condition;
use Aptenex\Upp\Parser\Structure\Defaults;
use Aptenex\Upp\Parser\Structure\Period;
use Aptenex\Upp\Parser\Structure\Rate;

class DefaultsParser
{

    /**
     * @param array $defaults
     * @return Defaults
     */
    public function parse(array $defaults)
    {
        $d = new Defaults();

        $d->setDamageDeposit(ArrayAccess::get('damageDeposit', $defaults, null));
        $d->setDamageDepositSplitMethod(ArrayAccess::get('damageDepositSplitMethod', $defaults, null));
        $d->setDamageDepositCalculationMethod(ArrayAccess::get('damageDepositCalculationMethod', $defaults, Rate::METHOD_FIXED));

        $d->setMinimumNights(ArrayAccess::get('minimumNights', $defaults, null));
        $d->setBalanceDaysBeforeArrival(ArrayAccess::get('balanceDaysBeforeArrival', $defaults, null));
        $d->setDepositSplitPercentage(ArrayAccess::get('depositSplitPercentage', $defaults, null));

        $d->setBookableType(ArrayAccess::get('bookableType', $defaults, Period::BOOKABLE_TYPE_DEFAULT));

        $d->setDaysRequiredInAdvanceForBooking(ArrayAccess::get('daysRequiredInAdvanceForBooking', $defaults, null));

        $d->setExtraNightAlterationStrategyUseGlobalNights(ArrayAccess::get('extraNightAlterationStrategyUseGlobalNights', $defaults, null));

        return $d;
    }

}