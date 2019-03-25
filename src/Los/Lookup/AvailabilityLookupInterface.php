<?php

namespace Aptenex\Upp\Los\Lookup;

interface AvailabilityLookupInterface
{

    /**
     * @param string $date
     * @return bool
     */
    public function isAvailable(string $date): bool;

}