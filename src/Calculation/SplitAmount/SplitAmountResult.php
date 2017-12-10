<?php

namespace Aptenex\Upp\Calculation\SplitAmount;

use Money\Money;

class SplitAmountResult
{

    /**
     * @var Money
     */
    private $deposit;

    /**
     * @var Money
     */
    private $balance;

    /**
     * @var Money
     */
    private $damageDeposit;

    /**
     * @var string
     */
    private $damageDepositSplitMethod;

    /**
     * @return Money
     */
    public function getDeposit()
    {
        return $this->deposit;
    }

    /**
     * @param Money $deposit
     */
    public function setDeposit($deposit)
    {
        $this->deposit = $deposit;
    }

    /**
     * @return Money
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param Money $balance
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
    }

    /**
     * @return Money
     */
    public function getDamageDeposit()
    {
        return $this->damageDeposit;
    }

    /**
     * @param Money $damageDeposit
     */
    public function setDamageDeposit($damageDeposit)
    {
        $this->damageDeposit = $damageDeposit;
    }

    /**
     * @return string
     */
    public function getDamageDepositSplitMethod()
    {
        return $this->damageDepositSplitMethod;
    }

    /**
     * @param string $damageDepositSplitMethod
     */
    public function setDamageDepositSplitMethod($damageDepositSplitMethod)
    {
        $this->damageDepositSplitMethod = $damageDepositSplitMethod;
    }

}