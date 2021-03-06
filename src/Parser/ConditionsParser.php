<?php

namespace Aptenex\Upp\Parser;

use Aptenex\Upp\Exception\InvalidPricingConfigException;
use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\Structure\Condition;
use Aptenex\Upp\Parser\Structure\PricingConfig;

class ConditionsParser extends BaseChildParser
{

    /**
     * @param array $conditionsArray
     * @return Condition[]
     */
    public function parse(array $conditionsArray)
    {
        $p = [];

        foreach($conditionsArray as $index => $condition) {
            $p[] = $this->parseCondition($condition, $index);
        }

        return $p;
    }

    private function parseCondition($conditionData, $index)
    {
        switch (ArrayAccess::get('type', $conditionData, null)) {

            case Condition::TYPE_DATE:

                $c = new Condition\DateCondition();

                $c->setStartDate(ArrayAccess::get('startDate', $conditionData, null));
                $c->setEndDate(ArrayAccess::get('endDate', $conditionData, null));
                $c->setArrivalDays(ArrayAccess::get('arrivalDays', $conditionData, []));
                $c->setDepartureDays(ArrayAccess::get('departureDays', $conditionData, []));

                break;

            case Condition::TYPE_MULTI_DATE:

                $c = new Condition\MultiDateCondition();

                $c->setStrategy(ArrayAccess::get('strategy', $conditionData, Condition\MultiDateCondition::STRATEGY_MATCH));
                $c->setDates(ArrayAccess::get('dates', $conditionData, []));

                break;

            case Condition::TYPE_NIGHTS:

                $c = new Condition\NightsCondition();

                $c->setMinimum(ArrayAccess::get('minimum', $conditionData, null));
                $c->setMaximum(ArrayAccess::get('maximum', $conditionData, null));

                break;

            case Condition::TYPE_WEEKS:

                $c = new Condition\WeeksCondition();

                $c->setMinimum(ArrayAccess::get('minimum', $conditionData, null));
                $c->setMaximum(ArrayAccess::get('maximum', $conditionData, null));

                break;

            case Condition::TYPE_MONTHS:

                $c = new Condition\MonthsCondition();

                $c->setMinimum(ArrayAccess::get('minimum', $conditionData, null));
                $c->setMaximum(ArrayAccess::get('maximum', $conditionData, null));

                break;

            case Condition::TYPE_GUESTS:

                $c = new Condition\GuestsCondition();

                $c->setMinimum(ArrayAccess::get('minimum', $conditionData, null));
                $c->setMaximum(ArrayAccess::get('maximum', $conditionData, null));

                // we need to update this flag to show what is the lowest minimum for the guest count
                // as this will be used for los generation to determine when to increment the
                // occupancy count, getting an accurate figure for this will save a lot of pricing iterations

                $gmFlag = PricingConfig::FLAG_HAS_PER_GUEST_MODIFIER;
                if (!$this->getConfig()->hasFlag($gmFlag)) {
                    $this->getConfig()->addFlag($gmFlag, (int) $c->getMinimum());
                } else {
                    // Flag has already been set once so we need to check and update if applicable
                    $currentMin = $this->getConfig()->getFlag($gmFlag);
                    if ($c->getMinimum() > $currentMin) {
                        $this->getConfig()->setFlag($gmFlag, (int) $c->getMinimum()); // Update
                    }
                }

                break;

            case Condition::TYPE_WEEKDAYS:

                $c = new Condition\WeekdaysCondition();

                $c->setWeekdays(ArrayAccess::get('weekdays', $conditionData, []));

                break;

            case Condition::TYPE_LUNAR_PHASE:

                $c = new Condition\LunarPhaseCondition();

                $c->setPhases(ArrayAccess::get('phases', $conditionData, []));
                $c->setDateType(ArrayAccess::get('dateType', $conditionData, Condition\LunarPhaseCondition::BOOKING_DATE));

                break;

            case Condition::TYPE_DISTRIBUTION:

                $c = new Condition\DistributionCondition();

                $c->setChannels(ArrayAccess::get('distributionChannels', $conditionData, []));

                break;

            case Condition::TYPE_BOOKING_DAYS:

                $c = new Condition\BookingDaysCondition();

                $c->setMinimum(ArrayAccess::get('minimum', $conditionData, null));
                $c->setMaximum(ArrayAccess::get('maximum', $conditionData, null));

                break;

            default:

                throw new InvalidPricingConfigException(sprintf(
                    "The 'type' parameter is invalid/unspecified at condition index %s",
                    $index
                ));
        }

        // Defaults
        $c->setType(ArrayAccess::get('type', $conditionData));
        $c->setInverse(ArrayAccess::get('inverse', $conditionData, false));
        $c->setModifyRatePerUnit(ArrayAccess::get('modifyRatePerUnit', $conditionData, true));

        return $c;
    }

}