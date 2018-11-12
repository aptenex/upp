<?php

namespace Aptenex\Upp\Calculation\SplitAmount;

use Aptenex\Upp\Util\MoneyUtils;
use Money\Money;

class GuestSplitOverview
{

    const DEPOSIT_CALCULATION_TYPE_DEFAULT = 'AUTOMATIC';
    const DEPOSIT_CALCULATION_TYPE_AUTOMATIC = 'AUTOMATIC';
    const DEPOSIT_CALCULATION_TYPE_FIXED = 'FIXED';

    /**
     * @var Money
     */
    private $deposit;

    /**
     * @var string
     */
    private $depositCalculationType = self::DEPOSIT_CALCULATION_TYPE_AUTOMATIC;

    /**
     * @var \DateTime
     */
    private $depositDueDate;

    /**
     * @var Money
     */
    private $balance;

    /**
     * @var \DateTime
     */
    private $balanceDueDate;

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
     * @return \DateTime
     */
    public function getDepositDueDate()
    {
        return $this->depositDueDate;
    }

    /**
     * @param \DateTime $depositDueDate
     */
    public function setDepositDueDate($depositDueDate)
    {
        $this->depositDueDate = $depositDueDate;
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
     * @return \DateTime
     */
    public function getBalanceDueDate()
    {
        return $this->balanceDueDate;
    }

    /**
     * @param \DateTime $balanceDueDate
     */
    public function setBalanceDueDate($balanceDueDate)
    {
        $this->balanceDueDate = $balanceDueDate;
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

    /**
     * @return \DateTime
     */
    private function getNowDate()
    {
        return new \DateTime(date("Y-m-d"), new \DateTimeZone('UTC'));
    }

    /**
     * @return string
     */
    public function getDepositCalculationType()
    {
        return $this->depositCalculationType;
    }

    /**
     * @param string $depositCalculationType
     */
    public function setDepositCalculationType($depositCalculationType)
    {
        $this->depositCalculationType = $depositCalculationType;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        $dDueDate = null;
        $dDueNow = true;
        if ($this->getDepositDueDate() instanceof \DateTime) {
            $dDueDate = $this->getDepositDueDate()->format("Y-m-d");
            $dDueNow = $this->getNowDate() >= $this->getDepositDueDate();
        }

        $bDueDate = null;
        $bDueNow = false;
        if ($this->getBalanceDueDate() instanceof \DateTime) {
            $bDueDate = $this->getBalanceDueDate()->format("Y-m-d");
            $bDueNow = $this->getNowDate() >= $this->getBalanceDueDate();
        }

        return [
            'deposit' => [
                'amount' => MoneyUtils::getConvertedAmount($this->getDeposit()),
                'calculationType' => $this->getDepositCalculationType(),
                'dueDate' => $dDueDate,
                'dueNow'  => $dDueNow
            ],
            'balance' => [
                'amount' => MoneyUtils::getConvertedAmount($this->getBalance()),
                'dueDate' => $bDueDate,
                'dueNow'  => $bDueNow
            ],
            'damageDepositSplitMethod' => $this->getDamageDepositSplitMethod()
        ];
    }

}