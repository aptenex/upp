<?php

namespace Aptenex\Upp\Los;

use Aptenex\Upp\Context\PricingContext;

class LosOptions
{

    /**
     * @var \DateTime
     */
    private $startDate;

    /**
     * @var \DateTime
     */
    private $endDate;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var int
     */
    private $defaultMinStay = 0;

    /**
     * @var int
     */
    private $defaultMaxStay = 30;

    /**
     * Even if the max stay is above say 60 - cut off/pad all rates to this amount
     *
     * @var int
     */
    private $maximumStayRateLength = 30;

    /**
     * Whether to brute force the generation and not to take into account availability & other factors etc
     *
     * @var bool
     */
    private $forceFullGeneration = false;
    
    /**
     * Wether all debugging/exception notices can be included in with the LosRecords. This is useful for knowing why specific dates may fail to calculate.
     *
     * @var bool
     */
    private $debugMode = false;
    
    /**
     * Supports ISO8601 date.
     * {date} OR
     * {date},{occupancy} OR
     * /{regularExpress}/ where the subject is the pattern below.
     * When provided such as [ "2019-09-01,3" ]
     *
     * @var array|null
     */
    private $forceDebugOnDate;

    /**
     * PricingContext Mode
     *
     * @var string
     */
    private $pricingContextMode = PricingContext::CALCULATION_MODE_NORMAL;

    /**
     * @param string $currency
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     */
    public function __construct(?string $currency = null, ?\DateTime $startDate= null, ?\DateTime $endDate= null)
    {
        $this->currency = $currency;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
    
    /**
     * @return LosOptions
     */
    public static function create(): LosOptions
    {
        return new self();
    }

    /**
     * @return int
     */
    public function getDefaultMinStay(): int
    {
        return $this->defaultMinStay;
    }
    
    /**
     * @param int $defaultMinStay
     * @return LosOptions
     */
    public function setDefaultMinStay(int $defaultMinStay): LosOptions
    {
        $this->defaultMinStay = $defaultMinStay;
        return $this;
    }

    /**
     * @return int
     */
    public function getDefaultMaxStay(): int
    {
        return $this->defaultMaxStay;
    }
    
    /**
     * @param int $defaultMaxStay
     * @return LosOptions
     */
    public function setDefaultMaxStay(int $defaultMaxStay): self
    {
        $this->defaultMaxStay = $defaultMaxStay;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }
    

    /**
     * @return \DateTime
     */
    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

    /**
     * @return int
     */
    public function getMaximumStayRateLength(): int
    {
        return $this->maximumStayRateLength;
    }
    
    /**
     * @param int $maximumStayRateLength
     * @return LosOptions
     */
    public function setMaximumStayRateLength(int $maximumStayRateLength): self
    {
        $this->maximumStayRateLength = $maximumStayRateLength;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return bool
     */
    public function isForceFullGeneration(): bool
    {
        return $this->forceFullGeneration;
    }
    
    /**
     * @param bool $forceFullGeneration
     * @return LosOptions
     */
    public function setForceFullGeneration(bool $forceFullGeneration): self
    {
        $this->forceFullGeneration = $forceFullGeneration;
        return $this;
    }

    /**
     * @return string
     */
    public function getPricingContextMode(): string
    {
        return $this->pricingContextMode;
    }
    
    /**
     * @param string $pricingContextMode
     * @return LosOptions
     */
    public function setPricingContextMode(string $pricingContextMode): self
    {
        $this->pricingContextMode = $pricingContextMode;
        return $this;
    }
    
    /**
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return $this->debugMode;
    }
    
    /**
     * @param bool $debugMode
     * @return LosOptions
     */
    public function setDebugMode(bool $debugMode): self
    {
        $this->debugMode = $debugMode;
        return $this;
    }
    
    /**
     * @return array
     */
    public function getForceDebugOnDate(): ?array
    {
        return $this->forceDebugOnDate;
    }
    
    /**
     * @param array $forceDebugOnDate
     */
    public function setForceDebugOnDate(array $forceDebugOnDate): void
    {
        $this->forceDebugOnDate = $forceDebugOnDate;
    }
    
    
    
    public function __toArray(): array
    {
        return [
            'startDate' => $this->getStartDate()->format('Y-m-d'),
            'endDate' => $this->getEndDate()->format('Y-m-d'),
            'currency' => $this->getCurrency(),
            'defaultMinStay' => $this->getDefaultMinStay(),
            'defaultMaxStay' => $this->getDefaultMaxStay(),
            'forceFullGeneration'   => $this->isForceFullGeneration(),
            'forceDebugOnDate' => $this->getForceDebugOnDate(),
            'debugMode' => $this->isDebugMode(),
            'pricingContextMode' => $this->getPricingContextMode()
        ];
    }
    
    /**
     * @param string $currency
     * @return LosOptions
     */
    public function setCurrency(string $currency): LosOptions
    {
        $this->currency = $currency;
        
        return $this;
    }
    
    /**
     * @param \DateTime $startDate
     * @return LosOptions
     */
    public function setStartDate(\DateTime $startDate): LosOptions
    {
        $this->startDate = $startDate;
        
        return $this;
    }
    
    /**
     * @param \DateTime $endDate
     * @return LosOptions
     */
    public function setEndDate(\DateTime $endDate): LosOptions
    {
        $this->endDate = $endDate;
        
        return $this;
    }
    
    
}