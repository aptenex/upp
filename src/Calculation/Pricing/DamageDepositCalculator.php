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

        if (!$defaults->hasDamageDeposit()) {
            return; // Nothing to calculate
        }

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

        $fp->setDamageDeposit(MoneyTools::applyCalculationMethodToAmount(
            $amountOrPercentage,
            $defaults->getDamageDepositCalculationMethod(),
            $fp->getBasePrice()
        ));

        if ($fp->getDamageDeposit()->getAmount() > 0) {
            $this->applyAdjustment($fp);
        }
    }

    private function applyAdjustment(FinalPrice $fp)
    {
        $defaults = $fp->getCurrencyConfigUsed()->getDefaults();

        $isOnArrival = $defaults->getDamageDepositSplitMethod() === SplitMethod::ON_ARRIVAL;

        $adjustment = new AdjustmentAmount(
            $fp->getDamageDeposit(),
            'DAMAGE_DEPOSIT',
            sprintf('Damage Deposit%s', $isOnArrival ? ' (On Arrival)': ''),
            Operand::OP_ADDITION,
            AdjustmentAmount::TYPE_DAMAGE_DEPOSIT,
            $isOnArrival ? AdjustmentAmount::PRICE_GROUP_ARRIVAL : AdjustmentAmount::PRICE_GROUP_TOTAL,
            $fp->getSplitDetails()->getDamageDepositSplitMethod(),
            false
        );

        $fp->addAdjustment($adjustment);
    }

}