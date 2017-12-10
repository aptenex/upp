<?php

namespace Aptenex\Upp\Calculation\Pricing;

use Aptenex\Upp\Util\MoneyUtils;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Calculation\Night;
use Aptenex\Upp\Helper\MoneyTools;
use Aptenex\Upp\Parser\Structure\Rate;
use Money\Money;

class BasicRateCalculator
{


    /**
     * @param FinalPrice $fp
     */
    public function compute(FinalPrice $fp)
    {
        foreach($fp->getStay()->getPeriodsUsed() as $period) {

            $rateConfig = $period->getControlItemConfig()->getRate();

            $nightsMatched = count($period->getMatchedNights());

            $rateAmount = \Aptenex\Upp\Util\MoneyUtils::fromString($rateConfig->getAmount(), $fp->getCurrency());

            $rateConvertedWeekly = MoneyUtils::getConvertedAmount($rateAmount) / 7;

            $nightlyFromWeekly = \Aptenex\Upp\Util\MoneyUtils::fromString($rateConvertedWeekly, $fp->getCurrency());

            if ($rateAmount->getAmount() === 0 || $nightsMatched === 0) {
                continue;
            }

            if ($rateConfig->getType() === Rate::TYPE_WEEKLY) {

                // Since a partial week alteration can modify the extra days - we
                // need to allocate the weeks nightly rate for each set of 7 days.
                // This means that if any extra nights are altered it won't mess up
                // the 7 day nightly adding up to the weeks price

                // Bring the weekly rate up to the amount of weeks
                $weeksMatched = floor($nightsMatched / 7);
                $singleWeekAllocation = $rateAmount->allocateTo(7);

                $nights = array_values($period->getMatchedNights());

                for ($i = 0; $i < count($nights); $i++) {
                    /** @var Night $night */
                    $night = $nights[$i];

                    if ($i > $weeksMatched * 7) {
                        $night->setCost($nightlyFromWeekly);
                    } else {
                        $night->setCost($singleWeekAllocation[$i % 7]);
                    }
                }

            } else {
                // Nightly? That means the rate amount is nightly - apply to every night
                foreach($period->getMatchedNights() as $key => $night) {
                    $night->setCost($rateAmount);
                }
            }
        }
    }

    /**
     * @param Night $day
     * @param string $currency
     *
     * @return Money
     */
    public function computeNightlyRate(Night $day, $currency)
    {
        $amount = \Aptenex\Upp\Util\MoneyUtils::newMoney(0, $currency);

        // SINCE MONEY CAN ONLY BE ALLOCATED AND NOT DIVIDE WE MUST ALLOCATE TO THE SET OF NIGHTS
        // WITHIN THE SAME CONTROL ITEM

        $rateConfig = $day->getPeriodControlItem()->getControlItemConfig()->getRate();

        switch ($rateConfig->getType()) {

            case Rate::TYPE_WEEKLY:

                $amount = \Aptenex\Upp\Util\MoneyUtils::fromString($rateConfig->getAmount(), $currency);


                break;

            case Rate::TYPE_NIGHTLY:
            case Rate::TYPE_ADJUSTMENT:
            default:
                $amount = \Aptenex\Upp\Util\MoneyUtils::fromString($rateConfig->getAmount(), $currency);


        }

        return $amount;
    }

}