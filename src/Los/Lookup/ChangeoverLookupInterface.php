<?php

namespace Los\Lookup;

interface ChangeoverLookupInterface
{

    /**
     * @param string $date
     * @return bool
     */
    public function canArrive(string $date): bool;

    /**
     * @param string $date
     * @return bool
     */
    public function canDepart(string $date): bool;

    /**
     * @param string $date
     * @return bool
     */
    public function canArriveOrDepart(string $date): bool;

    /**
     * @param string $date
     * @return bool
     */
    public function canArriveAndDepart(string $date): bool;

}