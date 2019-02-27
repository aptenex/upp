<?php

namespace Los;

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
    private $singleCurrency;

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
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     */
    public function __construct(\DateTime $startDate, \DateTime $endDate)
    {
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
     * @return bool
     */
    public function hasSingleCurrency(): bool
    {
        return !empty($this->singleCurrency);
    }

    /**
     * @return string
     */
    public function getSingleCurrency(): string
    {
        return $this->singleCurrency;
    }

    /**
     * @param string $singleCurrency
     */
    public function setSingleCurrency(string $singleCurrency)
    {
        $this->singleCurrency = $singleCurrency;
    }

}