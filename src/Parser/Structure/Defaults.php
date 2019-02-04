<?php

namespace Aptenex\Upp\Parser\Structure;

class Defaults
{

    /**
     * @var string|null
     */
    protected $damageDeposit = null;

    /**
     * @var int|null
     */
    protected $minimumNights = null;

    /**
     * @var int|null
     */
    protected $balanceDaysBeforeArrival = null;

    /**
     * @var int|null
     */
    protected $depositSplitPercentage = null;

    /**
     * @var string
     */
    protected $damageDepositSplitMethod = SplitMethod::ON_TOTAL;

    /**
     * @var string
     */
    protected $damageDepositCalculationMethod = Rate::METHOD_FIXED;

    /**
     * @var boolean
     */
    protected $extraNightAlterationStrategyUseGlobalNights = false;

    /**
     * @var string
     */
    protected $bookableType = Period::BOOKABLE_TYPE_DEFAULT;

    /**
     * @var null|int
     */
    protected $daysRequiredInAdvanceForBooking = null;

    /**
     * @var float|null
     */
    protected $perPetPerStay = null;

    /**
     * @var float|null
     */
    protected $perPetPerNight = null;

    /**
     * @var string
     */
    protected $perPetSplitMethod = SplitMethod::ON_TOTAL;

    /**
     * @return bool
     */
    public function hasDamageDeposit()
    {
        return ((float) $this->getDamageDeposit()) > 0;
    }

    /**
     * @return null|string
     */
    public function getDamageDeposit()
    {
        return $this->damageDeposit;
    }

    /**
     * @param null|string $damageDeposit
     */
    public function setDamageDeposit($damageDeposit)
    {
        $this->damageDeposit = $damageDeposit;
    }

    /**
     * @return bool
     */
    public function hasMinimumNights()
    {
        return !is_null($this->getMinimumNights());
    }

    /**
     * @return int|null
     */
    public function getMinimumNights()
    {
        return $this->minimumNights;
    }

    /**
     * @param int|null $minimumNights
     */
    public function setMinimumNights($minimumNights)
    {
        $this->minimumNights = (int) $minimumNights;
    }

    /**
     * @return float|null
     */
    public function getDepositSplitPercentage()
    {
        return $this->depositSplitPercentage;
    }

    /**
     * @return bool
     */
    public function hasDepositSplitPercentage()
    {
        return !is_null($this->depositSplitPercentage) && $this->depositSplitPercentage > 0;
    }

    /**
     * @param float|null $depositSplitPercentage
     */
    public function setDepositSplitPercentage($depositSplitPercentage)
    {
        $this->depositSplitPercentage = $depositSplitPercentage;
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
     * @return int|null
     */
    public function getBalanceDaysBeforeArrival()
    {
        return $this->balanceDaysBeforeArrival;
    }

    /**
     * @param int|null $balanceDaysBeforeArrival
     */
    public function setBalanceDaysBeforeArrival($balanceDaysBeforeArrival)
    {
        $this->balanceDaysBeforeArrival = $balanceDaysBeforeArrival;
    }

    /**
     * @return boolean
     */
    public function isExtraNightAlterationStrategyUseGlobalNights()
    {
        return $this->extraNightAlterationStrategyUseGlobalNights;
    }

    /**
     * @param boolean $extraNightAlterationStrategyUseGlobalNights
     */
    public function setExtraNightAlterationStrategyUseGlobalNights($extraNightAlterationStrategyUseGlobalNights)
    {
        $this->extraNightAlterationStrategyUseGlobalNights = $extraNightAlterationStrategyUseGlobalNights;
    }

    /**
     * @return string
     */
    public function getDamageDepositCalculationMethod()
    {
        return $this->damageDepositCalculationMethod;
    }

    /**
     * @param string $damageDepositCalculationMethod
     */
    public function setDamageDepositCalculationMethod($damageDepositCalculationMethod)
    {
        $this->damageDepositCalculationMethod = $damageDepositCalculationMethod;
    }

    /**
     * @return string
     */
    public function getBookableType()
    {
        return $this->bookableType;
    }

    /**
     * @param string $bookableType
     */
    public function setBookableType($bookableType)
    {
        $this->bookableType = $bookableType;
    }

    /**
     * @return int|null
     */
    public function getDaysRequiredInAdvanceForBooking()
    {
        return $this->daysRequiredInAdvanceForBooking;
    }

    /**
     * @return bool
     */
    public function hasDaysRequiredInAdvanceForBooking()
    {
        return ((int) $this->daysRequiredInAdvanceForBooking) > 0;
    }

    /**
     * @param int|null $daysRequiredInAdvanceForBooking
     */
    public function setDaysRequiredInAdvanceForBooking($daysRequiredInAdvanceForBooking)
    {
        $this->daysRequiredInAdvanceForBooking = $daysRequiredInAdvanceForBooking;
    }

    /**
     * @return float|null
     */
    public function getPerPetPerStay()
    {
        return $this->perPetPerStay;
    }

    /**
     * @param float|null $perPetPerStay
     */
    public function setPerPetPerStay($perPetPerStay)
    {
        $this->perPetPerStay = (float) $perPetPerStay;
    }

    /**
     * @return bool
     */
    public function hasPerPetPerStay(): bool
    {
        return ((float) $this->perPetPerStay) > 0;
    }

    /**
     * @return float|null
     */
    public function getPerPetPerNight()
    {
        return $this->perPetPerNight;
    }

    /**
     * @param float|null $perPetPerNight
     */
    public function setPerPetPerNight($perPetPerNight)
    {
        $this->perPetPerNight = (float) $perPetPerNight;
    }

    /**
     * @return bool
     */
    public function hasPerPetPerNight(): bool
    {
        return ((float) $this->perPetPerNight) > 0;
    }

    /**
     * @return string
     */
    public function getPerPetSplitMethod()
    {
        return $this->perPetSplitMethod;
    }

    /**
     * @param string $perPetSplitMethod
     */
    public function setPerPetSplitMethod($perPetSplitMethod)
    {
        $this->perPetSplitMethod = $perPetSplitMethod;
    }

    /**
     * @return array
     */
    public function __toArray(): array
    {
        return [
            'minimumNights'                               => $this->getMinimumNights(),
            'perPetPerStay'                               => $this->getPerPetPerStay(),
            'perPetPerNight'                              => $this->getPerPetPerStay(),
            'perPetSplitMethod'                           => $this->getPerPetSplitMethod(),
            'damageDeposit'                               => $this->getDamageDeposit(),
            'damageDepositCalculationMethod'              => $this->getDamageDepositCalculationMethod(),
            'damageDepositSplitMethod'                    => $this->getDamageDepositSplitMethod(),
            'daysRequiredInAdvanceForBooking'             => $this->getDaysRequiredInAdvanceForBooking(),
            'balanceDaysBeforeArrival'                    => $this->getBalanceDaysBeforeArrival(),
            'depositSplitPercentage'                      => $this->getDepositSplitPercentage(),
            'extraNightAlterationStrategyUseGlobalNights' => $this->isExtraNightAlterationStrategyUseGlobalNights()
        ];
    }

}