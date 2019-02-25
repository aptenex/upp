<?php

namespace Los\Lookup;

class LookupDirector
{

    /**
     * @var AvailabilityLookupInterface
     */
   private $availabilityLookup;

    /**
     * @var ChangeoverLookupInterface
     */
   private $changeoverLookup;

    /**
     * @var MinimumStayLookupInterface
     */
   private $minimumStayLookup;

    /**
     * @var MaximumStayLookupInterface
     */
   private $maximumStayLookup;

    /**
     * @param AvailabilityLookupInterface $availabilityLookup
     * @param ChangeoverLookupInterface $changeoverLookup
     * @param MinimumStayLookupInterface $minimumStayLookup
     * @param MaximumStayLookupInterface $maximumStayLookup
     */
    public function __construct(AvailabilityLookupInterface $availabilityLookup, ChangeoverLookupInterface $changeoverLookup, MinimumStayLookupInterface $minimumStayLookup, MaximumStayLookupInterface $maximumStayLookup)
    {
        $this->availabilityLookup = $availabilityLookup;
        $this->changeoverLookup = $changeoverLookup;
        $this->minimumStayLookup = $minimumStayLookup;
        $this->maximumStayLookup = $maximumStayLookup;
    }

    /**
     * @return AvailabilityLookupInterface
     */
    public function getAvailabilityLookup(): AvailabilityLookupInterface
    {
        return $this->availabilityLookup;
    }

    /**
     * @return ChangeoverLookupInterface
     */
    public function getChangeoverLookup(): ChangeoverLookupInterface
    {
        return $this->changeoverLookup;
    }

    /**
     * @return MinimumStayLookupInterface
     */
    public function getMinimumStayLookup(): MinimumStayLookupInterface
    {
        return $this->minimumStayLookup;
    }

    /**
     * @return MaximumStayLookupInterface
     */
    public function getMaximumStayLookup(): MaximumStayLookupInterface
    {
        return $this->maximumStayLookup;
    }

}