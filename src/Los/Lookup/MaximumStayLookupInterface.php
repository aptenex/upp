<?php

namespace Aptenex\Upp\Los\Lookup;

interface MaximumStayLookupInterface
{

    /**
     * @param string $date
     * @return int
     */
    public function getMaximumStay($date): int;

}