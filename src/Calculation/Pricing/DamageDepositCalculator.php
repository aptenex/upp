<?php

namespace Aptenex\Upp\Calculation\Pricing;

use Aptenex\Upp\Calculation\AdjustmentAmount;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Helper\MoneyTools;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Parser\Structure\SplitMethod;
use Aptenex\Upp\Util\MoneyUtils;

class DamageDepositCalculator
{

    public function calculateAndApplyAdjustment(FinalPrice $fp)
    {
        $defaults = $fp->getCurrencyConfigUsed()->getDefaults();

        $amountOrPercentage = $defaults->getDamageDeposit();

        foreach ($fp->getStay()->getPeriodsUsed() as $period) {
            if ($period->containsArrivalDayInMatchedNights()) {

                $rate = $period->getControlItemConfig()->getRate();
                if ($rate->hasDamageDeposit()) {
                    $amountOrPercentage = $rate->getDamageDeposit();
                }

                // This will override everything else
                if ($period->getRate()->hasDamageDepositOverride()) {
                    $amountOrPercentage = MoneyUtils::getConvertedAmount($period->getRate()->getDamageDepositOverride());
                }
            }
        }

        if ($defaults->getDamageDepositSplitMethod() === SplitMethod::ON_ARRIVAL) {
            // This will turn into a 'note' anyway
            $this->applyAdjustment($fp);
        } else {
            $fp->setDamageDeposit(MoneyTools::applyCalculationMethodToAmount(
                $amountOrPercentage,
                $defaults->getDamageDepositCalculationMethod(),
                $fp->getBasePrice()
            ));

            if ($fp->getDamageDeposit()->getAmount() > 0) {
                $this->applyAdjustment($fp);
            }
        }
    }

    private function applyAdjustment(FinalPrice $fp)
    {
        $defaults = $fp->getCurrencyConfigUsed()->getDefaults();

        $isOnArrival = $defaults->getDamageDepositSplitMethod() === SplitMethod::ON_ARRIVAL;

        $adjustment = new AdjustmentAmount(
            $fp->getDamageDeposit(),
            'Damage Deposit',
            sprintf('Damage Deposit%s', $isOnArrival ? ' (On Arrival)': ''),
            Operand::OP_ADDITION,
            AdjustmentAmount::TYPE_DAMAGE_DEPOSIT,
            AdjustmentAmount::PRICE_GROUP_TOTAL,
            $fp->getSplitDetails()->getDamageDepositSplitMethod(),
            false
        );

        $fp->addAdjustment($adjustment);
    }

}