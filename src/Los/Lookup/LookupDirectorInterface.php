<?php

namespace Aptenex\Upp\Los\Lookup;

interface LookupDirectorInterface
{

    /**
     * @return AvailabilityLookupInterface
     */
    public function getAvailabilityLookup(): AvailabilityLookupInterface;

    /**
     * @return ChangeoverLookupInterface
     */
    public function getChangeoverLookup(): ChangeoverLookupInterface;

    /**
     * @return MinimumStayLookupInterface
     */
    public function getMinimumStayLookup(): MinimumStayLookupInterface;

    /**
     * @return MaximumStayLookupInterface
     */
    public function getMaximumStayLookup(): MaximumStayLookupInterface;

    /**
     * @return MaxOccupancyLookupInterface
     */
    public function getMaxOccupancyLookup(): MaxOccupancyLookupInterface;

}