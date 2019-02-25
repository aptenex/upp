<?php

namespace Los\Lookup;

interface MinimumStayLookupInterface
{

    /**
     * @param string $date
     * @return int
     */
    public function getMinimumStay($date): int;

}