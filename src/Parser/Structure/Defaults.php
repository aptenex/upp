<?php

namespace Aptenex\Upp\Parser\Structure;

class Defaults
{

    public const PERIOD_SELECTION_STRATEGY_DEFAULT = 'DEFAULT';
    public const PERIOD_SELECTION_STRATEGY_ARRIVAL_EXCLUSIVE = 'ARRIVAL_EXCLUSIVE';

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
    protected $maximumNights = null;

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
     * @var boolean
     */
    protected $partialWeekAlterationStrategyUseGlobalNights = false;

    /**
     * @var boolean
     */
    protected $applyDiscountsToPartialMatches = false;

    /**
     * @var bool
     */
    protected $enablePriorityBasedModifiers = false;

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
     * @var boolean|null
     */
    protected $modifiersUseCategorizedCalculationOrder = false;

    /**
     * @var string|null
     */
    protected $periodSelectionStrategy = self::PERIOD_SELECTION_STRATEGY_DEFAULT;

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
        return $this->getMinimumNights() !== null;
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
     * @return bool
     */
    public function hasMaximumNights()
    {
        return $this->getMaximumNights() !== null && $this->getMaximumNights() !== 0 && !empty($this->getMaximumNights());
    }

    /**
     * @return int|null
     */
    public function getMaximumNights()
    {
        return $this->maximumNights;
    }

    /**
     * @param int|null $maximumNights
     */
    public function setMaximumNights($maximumNights)
    {
        $this->maximumNights = $maximumNights;
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
     * @return bool
     */
    public function isPartialWeekAlterationStrategyUseGlobalNights(): bool
    {
        return $this->partialWeekAlterationStrategyUseGlobalNights;
    }

    /**
     * @param bool $partialWeekAlterationStrategyUseGlobalNights
     */
    public function setPartialWeekAlterationStrategyUseGlobalNights(bool $partialWeekAlterationStrategyUseGlobalNights): void
    {
        $this->partialWeekAlterationStrategyUseGlobalNights = $partialWeekAlterationStrategyUseGlobalNights;
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
     * @return bool|null
     */
    public function isModifiersUseCategorizedCalculationOrder(): ?bool
    {
        return $this->modifiersUseCategorizedCalculationOrder;
    }

    /**
     * @param bool|null $modifiersUseCategorizedCalculationOrder
     */
    public function setModifiersUseCategorizedCalculationOrder(?bool $modifiersUseCategorizedCalculationOrder): void
    {
        $this->modifiersUseCategorizedCalculationOrder = $modifiersUseCategorizedCalculationOrder;
    }

    /**
     * @return string|null
     */
    public function getPeriodSelectionStrategy(): ?string
    {
        return $this->periodSelectionStrategy;
    }

    /**
     * @param string|null $periodSelectionStrategy
     */
    public function setPeriodSelectionStrategy(?string $periodSelectionStrategy): void
    {
        $this->periodSelectionStrategy = $periodSelectionStrategy;
    }

    /**
     * @return bool
     */
    public function isApplyDiscountsToPartialMatches(): bool
    {
        return $this->applyDiscountsToPartialMatches;
    }

    /**
     * @param bool $applyDiscountsToPartialMatches
     */
    public function setApplyDiscountsToPartialMatches(bool $applyDiscountsToPartialMatches): void
    {
        $this->applyDiscountsToPartialMatches = $applyDiscountsToPartialMatches;
    }

    /**
     * @return bool
     */
    public function isEnablePriorityBasedModifiers(): bool
    {
        return $this->enablePriorityBasedModifiers;
    }

    /**
     * @param bool $enablePriorityBasedModifiers
     */
    public function setEnablePriorityBasedModifiers(bool $enablePriorityBasedModifiers): void
    {
        $this->enablePriorityBasedModifiers = $enablePriorityBasedModifiers;
    }

    /**
     * @return array
     */
    public function __toArray(): array
    {
        return [
            'minimumNights'                                => $this->getMinimumNights(),
            'maximumNights'                                => $this->getMaximumNights(),
            'perPetPerStay'                                => $this->getPerPetPerStay(),
            'perPetPerNight'                               => $this->getPerPetPerNight(),
            'perPetSplitMethod'                            => $this->getPerPetSplitMethod(),
            'damageDeposit'                                => $this->getDamageDeposit(),
            'bookableType'                                 => $this->getBookableType(),
            'damageDepositCalculationMethod'               => $this->getDamageDepositCalculationMethod(),
            'damageDepositSplitMethod'                     => $this->getDamageDepositSplitMethod(),
            'daysRequiredInAdvanceForBooking'              => $this->getDaysRequiredInAdvanceForBooking(),
            'applyDiscountsToPartialMatches'               => $this->isApplyDiscountsToPartialMatches(),
            'balanceDaysBeforeArrival'                     => $this->getBalanceDaysBeforeArrival(),
            'depositSplitPercentage'                       => $this->getDepositSplitPercentage(),
            'periodSelectionStrategy'                      => $this->getPeriodSelectionStrategy(),
            'extraNightAlterationStrategyUseGlobalNights'  => $this->isExtraNightAlterationStrategyUseGlobalNights(),
            'partialWeekAlterationStrategyUseGlobalNights' => $this->isPartialWeekAlterationStrategyUseGlobalNights(),
            'modifiersUseCategorizedCalculationOrder'      => $this->isModifiersUseCategorizedCalculationOrder(),
            'enablePriorityBasedModifiers'                 => $this->isEnablePriorityBasedModifiers()
        ];
    }

}