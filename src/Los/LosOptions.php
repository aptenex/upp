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
    public function __construct(string $currency, \DateTime $startDate, \DateTime $endDate)
    {
        $this->currency = $currency;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
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
     */
    public function setDefaultMinStay(int $defaultMinStay)
    {
        $this->defaultMinStay = $defaultMinStay;
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
     */
    public function setDefaultMaxStay(int $defaultMaxStay)
    {
        $this->defaultMaxStay = $defaultMaxStay;
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
     */
    public function setMaximumStayRateLength(int $maximumStayRateLength)
    {
        $this->maximumStayRateLength = $maximumStayRateLength;
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
     */
    public function setForceFullGeneration(bool $forceFullGeneration)
    {
        $this->forceFullGeneration = $forceFullGeneration;
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
     */
    public function setPricingContextMode(string $pricingContextMode): void
    {
        $this->pricingContextMode = $pricingContextMode;
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
     */
    public function setDebugMode(bool $debugMode): void
    {
        $this->debugMode = $debugMode;
    }
    
}