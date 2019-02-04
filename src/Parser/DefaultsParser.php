<?php

namespace Aptenex\Upp\Parser;

use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\Structure\Defaults;
use Aptenex\Upp\Parser\Structure\Period;
use Aptenex\Upp\Parser\Structure\Rate;
use Aptenex\Upp\Parser\Structure\SplitMethod;

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
        $d->setDamageDepositSplitMethod(ArrayAccess::get('damageDepositSplitMethod', $defaults, SplitMethod::ON_DEPOSIT));
        $d->setDamageDepositCalculationMethod(ArrayAccess::get('damageDepositCalculationMethod', $defaults, Rate::METHOD_FIXED));

        if ($d->getDamageDepositCalculationMethod() === Rate::METHOD_PERCENTAGE && $d->getDamageDeposit() > 1) {
            $d->setDamageDeposit($d->getDamageDeposit() / 100); // Turn it into 0 - 100
        }

        $d->setPerPetPerStay(ArrayAccess::get('perPetPerStay', $defaults, 0));
        $d->setPerPetPerNight(ArrayAccess::get('perPetPerNight', $defaults, 0));
        $d->setPerPetSplitMethod(ArrayAccess::get('perPetSplitMethod', $defaults, SplitMethod::ON_TOTAL));

        $d->setMinimumNights(ArrayAccess::get('minimumNights', $defaults, null));
        $d->setBalanceDaysBeforeArrival(ArrayAccess::get('balanceDaysBeforeArrival', $defaults, null));
        $d->setDepositSplitPercentage(ArrayAccess::get('depositSplitPercentage', $defaults, null));

        $d->setBookableType(ArrayAccess::get('bookableType', $defaults, Period::BOOKABLE_TYPE_DEFAULT));

        $d->setDaysRequiredInAdvanceForBooking(ArrayAccess::get('daysRequiredInAdvanceForBooking', $defaults, null));

        $d->setExtraNightAlterationStrategyUseGlobalNights(ArrayAccess::get('extraNightAlterationStrategyUseGlobalNights', $defaults, null));

        return $d;
    }

}