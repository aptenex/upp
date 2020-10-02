<?php

namespace Aptenex\Upp\Parser\Structure;

class Tax
{

    // These Enums are not currently in use. We added them such that when we add flat fees for Taxes
    // They already exist and can be used.

    /**
     * @deprecated No taxes exist that are "fixed"
     *
     * @var string
     */
    public const METHOD_FIXED = 'fixed';
    public const METHOD_PERCENTAGE               = 'percentage';
    public const METHOD_FLAT_PER_GUEST           = 'flat_per_guest';
    public const METHOD_FLAT_PER_NIGHT           = 'flat_per_night';
    public const METHOD_FLAT_PER_GUEST_PER_NIGHT = 'flat_per_guest_per_night';


    public const TYPE_TAX                     = 'TYPE_TAX';
    public const TYPE_VAT                     = 'TYPE_VAT';
    public const TYPE_CITY_TAX                = 'TYPE_CITY_TAX';
    public const TYPE_GST                     = 'TYPE_GST';
    public const TYPE_GOVERNMENT_TAX          = 'TYPE_GOVERNMENT_TAX';
    public const TYPE_RESIDENTIAL_TAX         = 'TYPE_RESIDENTIAL_TAX';
    public const TYPE_LOCAL_COUNCIL_TAX       = 'TYPE_LOCAL_COUNCIL_TAX';
    public const TYPE_HOTEL_TAX               = 'TYPE_HOTEL_TAX';
    public const TYPE_LODGING_TAX             = 'TYPE_LODGING_TAX';
    public const TYPE_ROOM_TAX                = 'TYPE_ROOM_TAX';
    public const TYPE_TRANSIENT_OCCUPANCY_TAX = 'TYPE_TRANSIENT_OCCUPANCY_TAX';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

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
     * @var int|mixed|null
     */
    private $longStayExemption = 0;

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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return int|mixed|null
     */
    public function getLongStayExemption()
    {
        return $this->longStayExemption;
    }

    /**
     * @return bool
     */
    public function hasLongStayExemption(): bool
    {
        return !empty($this->longStayExemption) && $this->longStayExemption > 0;
    }

    /**
     * @param int|mixed|null $longStayExemption
     */
    public function setLongStayExemption($longStayExemption): void
    {
        $this->longStayExemption = $longStayExemption;
    }

    /**
     * @return array
     */
    public function __toArray(): array
    {
        return [
            'name'              => $this->getName(),
            'type'              => $this->getType(),
            'uuid'              => $this->getUuid(),
            'description'       => $this->getDescription(),
            'amount'            => $this->getAmount(),
            'calculationMethod' => $this->getCalculationMethod(),
            'longStayExemption' => $this->getLongStayExemption(),
            'includeBasePrice'  => $this->isIncludeBasePrice(),
            'includeExtras'     => $this->isIncludeExtras(),
            'extrasWhitelist'   => $this->getExtrasWhitelist(),
        ];
    }

}