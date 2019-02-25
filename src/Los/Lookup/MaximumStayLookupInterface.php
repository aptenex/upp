<?php

namespace Los\Lookup;

interface MaximumStayLookupInterface
{

    /**
     * @param string $date
     * @return int
     */
    public function getMaximumStay($date): int;

}