<?php

namespace Aptenex\Upp\Los\Lookup;

class MaxOccupancyFixedValue implements MaxOccupancyLookupInterface
{

    /**
     * @var int
     */
    private $maxOccupancy;


    public function __construct(int $maxOccupancy)
    {
        $this->maxOccupancy = $maxOccupancy;
    }

    /**
     * @return int
     */
    public function getMaxOccupancy(): int
    {
        return $this->maxOccupancy;
    }

}