<?php

namespace Aptenex\Upp\Parser\Structure;

class Tax
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $uuid;

    /**
     * @var string
     */
    private $description;

    /**
     * @var number
     */
    private $amount = 0;

    /**
     * @var string
     */
    private $calculationMethod = Rate::METHOD_PERCENTAGE;

    /**
     * @var bool
     */
    private $includeBasePrice = true;

    /**
     * @var bool
     */
    private $includeExtras = true;

    /**
     * @var array
     */
    private $extrasWhitelist = [];

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getCalculationMethod()
    {
        return $this->calculationMethod;
    }

    /**
     * @param string $calculationMethod
     */
    public function setCalculationMethod($calculationMethod)
    {
        $this->calculationMethod = $calculationMethod;
    }

    /**
     * @return number
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param number $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return boolean
     */
    public function isIncludeBasePrice()
    {
        return $this->includeBasePrice;
    }

    /**
     * @param boolean $includeBasePrice
     */
    public function setIncludeBasePrice($includeBasePrice)
    {
        $this->includeBasePrice = $includeBasePrice;
    }

    /**
     * @return boolean
     */
    public function isIncludeExtras()
    {
        return $this->includeExtras;
    }

    /**
     * @param boolean $includeExtras
     */
    public function setIncludeExtras($includeExtras)
    {
        $this->includeExtras = $includeExtras;
    }

    /**
     * @return array
     */
    public function getExtrasWhitelist()
    {
        return $this->extrasWhitelist;
    }

    /**
     * @param array $extrasWhitelist
     */
    public function setExtrasWhitelist($extrasWhitelist)
    {
        $this->extrasWhitelist = $extrasWhitelist;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return [
            'name' => $this->getName(),
            'uuid' => $this->getUuid(),
            'description' => $this->getDescription(),
            'amount' => $this->getAmount(),
            'calculationMethod' => $this->getCalculationMethod(),
            'includeBasePrice' => $this->isIncludeBasePrice(),
            'includeExtras' => $this->isIncludeExtras(),
            'extrasWhitelist' => $this->getExtrasWhitelist()
        ];
    }

}