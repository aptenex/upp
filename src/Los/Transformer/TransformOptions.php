<?php

namespace Aptenex\Upp\Los\Transformer;

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
     * @var string
     */
    private $priceReturnType = self::PRICE_RETURN_TYPE_TOTAL;

    /**
     * @return int
     */
    public function getBcomRoomId(): int
    {
        return $this->bcomRoomId;
    }

    /**
     * @param int $bcomRoomId
     */
    public function setBcomRoomId(int $bcomRoomId)
    {
        $this->bcomRoomId = $bcomRoomId;
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
     */
    public function setBcomRateId(int $bcomRateId)
    {
        $this->bcomRateId = $bcomRateId;
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
     */
    public function setPriceReturnType(string $priceReturnType)
    {
        $this->priceReturnType = $priceReturnType;
    }

}