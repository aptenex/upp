<?php

namespace Aptenex\Upp\Calculation\SplitAmount;

use Money\Money;
use Aptenex\Upp\Util\MoneyUtils;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Parser\Structure\DamageDeposit;

class SplitAmountProcessor
{

    /**
     * @var FinalPrice
     */
    private $fp;

    /**
     * @param FinalPrice $fp
     */
    public function __construct(FinalPrice $fp)
    {
        $this->fp = $fp;
    }

    /**
     * @param Money      $total
     * @param float      $depositPercentage
     * @param Money      $damageDeposit
     * @param string     $damageDepositSplitMethod
     * @param float|null $depositFixed
     *
     * @return SplitAmountResult
     */
    public function computeSplitAmount(Money $total, $depositPercentage, Money $damageDeposit, $damageDepositSplitMethod, $depositFixed = null)
    {
        $spr = new SplitAmountResult();
        $spr->setDamageDeposit($damageDeposit);
        $spr->setDamageDepositSplitMethod($damageDepositSplitMethod);

        $depositFixed = (float) $depositFixed;

        $splitFixed = MoneyUtils::newMoney(0, $total->getCurrency()->getCode());
        $splitPercentage = $depositPercentage;

        if ($depositFixed > 0) {
            $splitFixed = MoneyUtils::fromString($depositFixed, $total->getCurrency()->getCode());
        }

        $totalPriceMinusDamageDeposit = clone $total;

        if ($damageDeposit instanceof Money && $spr->getDamageDepositSplitMethod() !== 'ON_ARRIVAL') {
            $totalPriceMinusDamageDeposit = $total->subtract($damageDeposit);
        }

        /**
         * Determine the allocation ratio, if no percentage has been specified then the balance will get the
         * full remaining amount minus the fixed.
         */

        $ratio = [0, 100];
        if ($splitPercentage > 0) {
            $ratio = [$splitPercentage, 100 - $splitPercentage];
        }

        /**
         * @var Money $depositAllocated
         * @var Money $balanceAllocated
         */
        list($depositAllocated, $balanceAllocated) = $totalPriceMinusDamageDeposit->allocate($ratio);

        $spr->setDeposit($depositAllocated);
        $spr->setBalance($balanceAllocated);

        if (MoneyUtils::getConvertedAmount($damageDeposit) > 0) {
            list($ddDepositAllocated, $ddBalanceAllocated) = $damageDeposit->allocate($ratio);

            switch ($damageDepositSplitMethod) {

                case DamageDeposit::ON_DEPOSIT:

                    $newDeposit = $spr->getDeposit()->add($damageDeposit);
                    $spr->setDeposit($newDeposit);

                    break;

                case DamageDeposit::ON_BALANCE:

                    $newBalance = $spr->getBalance()->add($damageDeposit);
                    $spr->setBalance($newBalance);

                    break;

                case DamageDeposit::ON_TOTAL:

                    $spr->setDeposit($spr->getDeposit()->add($ddDepositAllocated));
                    $spr->setBalance($spr->getBalance()->add($ddBalanceAllocated));

                    break;

            }
        }

        // Sort out the fixed deposit stuff here
        if (MoneyUtils::getConvertedAmount($splitFixed) !== 0) {
            $spr->setDeposit($spr->getDeposit()->add($splitFixed));
            $spr->setBalance($spr->getBalance()->subtract($splitFixed));
        }

        return $spr;
    }

}