<?php

namespace Los\Transformer;

class TransformOptions
{

    /**
     * @var string
     */
    private $currency;

    /**
     * @var int
     */
    private $bcomRoomId;

    /**
     * @var int
     */
    private $bcomRateId;

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency)
    {
        $this->currency = $currency;
    }

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

}