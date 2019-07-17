<?php

namespace Aptenex\Upp\Los\Lookup;

interface AvailabilityLookupInterface
{

    /**
     * @param string $date
     * @return bool
     */
    public function isAvailable(string $date): bool;
    
    /**
     * @param string $startDate
     * @param string $endDate
     * @return bool
     */
    public function isAvailableBetween(string $startDate, string $endDate): bool;

}