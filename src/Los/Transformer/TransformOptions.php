<?php

namespace Aptenex\Upp\Los\Transformer;

class TransformOptions
{

    /**
     * @var int
     */
    private $bcomRoomId;

    /**
     * @var int
     */
    private $bcomRateId;

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