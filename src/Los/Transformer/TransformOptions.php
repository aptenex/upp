<?php

namespace Aptenex\Upp\Los\Transformer;

use Money\Currency;
use Money\Exchange;

class TransformOptions
{

    const PRICE_RETURN_TYPE_TOTAL = 'RETURN_TOTAL';
    const PRICE_RETURN_TYPE_BASE = 'RETURN_BASE';
    /**
     * @var int
     */
    private $bcomRoomId;

    /**
     * @var int
     */
    private $bcomRateId;
    
    /**
     * The Source Currency is set automatically.
     * @var Currency
     */
    private $sourceCurrency;
    
    /**
     * When transforming, do you want to convert the LOS generated to a new currency
     * @var Currency
     */
    private $targetCurrency;
    
    /**
     * You can provide an exchange and we will convert where possible using the Exchange.
     * @var Exchange
     */
    private $exchange;
    
    /**
     * This field allows us to modify all rates provided by a percentage. We would do this if you intend to reduce
     * or increase the price by a value. For example, 1.1 would be a 110% rate increase.
     * @var float
     */
    private $modifyRatePercentage;

    /**
     * @var string
     */
    private $priceReturnType = self::PRICE_RETURN_TYPE_TOTAL;

    /**
     * @var bool
     */
    private $indexRecordsByDate = false;

    /**
     * @return int
     */
    public function getBcomRoomId(): int
    {
        return $this->bcomRoomId;
    }
    
    /**
     * @param int $bcomRoomId
     * @return TransformOptions
     */
    public function setBcomRoomId(int $bcomRoomId) :self
    {
        $this->bcomRoomId = $bcomRoomId;
        return $this;
    }

    /**
     * @return int
     */
    public function getBcomRateId(): int
    {
        return $this->bcomRateId;
    }
    
    /**
     * @param int $bcomRateId
     * @return TransformOptions
     */
    public function setBcomRateId(int $bcomRateId):self
    {
        $this->bcomRateId = $bcomRateId;
        return $this;
    }

    /**
     * @return string
     */
    public function getPriceReturnType(): string
    {
        return $this->priceReturnType;
    }
    
    /**
     * @param string $priceReturnType
     * @return TransformOptions
     */
    public function setPriceReturnType(string $priceReturnType):self
    {
        $this->priceReturnType = $priceReturnType;
        return $this;
    }
    
    /**
     * @return Currency
     */
    public function getTargetCurrency(): ?Currency
    {
        return $this->targetCurrency;
    }
    
    /**
     * @param Currency $targetCurrency
     * @return TransformOptions
     */
    public function setTargetCurrency(Currency $targetCurrency):self
    {
        $this->targetCurrency = $targetCurrency;
        return $this;
    }
    
    /**
     * @return Exchange
     */
    public function getExchange(): Exchange
    {
        return $this->exchange;
    }
    
    /**
     * @param Exchange $exchange
     * @return TransformOptions
     */
    public function setExchange(Exchange $exchange):self
    {
        $this->exchange = $exchange;
        return $this;
    }
    
    /**
     * @param Currency $sourceCurrency
     * @return TransformOptions
     */
    public function setSourceCurrency(Currency $sourceCurrency): TransformOptions
    {
        $this->sourceCurrency = $sourceCurrency;
        
        return $this;
    }
    
    /**
     * Values should be to 100th place.  For example 1.1 is a 10% increase. 0.5 is 50%.
     * @param float $modifyRatePercentage
     * @return TransformOptions
     */
    public function setModifyRatePercentage(float $modifyRatePercentage): TransformOptions
    {
        $this->modifyRatePercentage = $modifyRatePercentage;
        
        return $this;
    }
    
    /**
     * @return float
     */
    public function getModifyRatePercentage(): ?float
    {
        return $this->modifyRatePercentage;
    }
    
    /**
     * @return Currency
     */
    public function getSourceCurrency(): Currency
    {
        return $this->sourceCurrency;
    }

    /**
     * @return bool
     */
    public function isIndexRecordsByDate(): bool
    {
        return $this->indexRecordsByDate;
    }

    /**
     * @param bool $indexRecordsByDate
     */
    public function setIndexRecordsByDate(bool $indexRecordsByDate): void
    {
        $this->indexRecordsByDate = $indexRecordsByDate;
    }

}