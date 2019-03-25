<?php

namespace Aptenex\Upp\Los\Lookup;

use Aptenex\Upp\Helper\ArrayAccess;

class MaxOccupancySchemaLookup implements MaxOccupancyLookupInterface
{

    /**
     * @var int
     */
    private $maxOccupancy;


    public function __construct(array $rentalSchemaData)
    {
        $this->maxOccupancy = (int) ArrayAccess::get('listing.maxOccupancy', $rentalSchemaData, 10);
    }

    /**
     * @return int
     */
    public function getMaxOccupancy(): int
    {
        return $this->maxOccupancy;
    }

}