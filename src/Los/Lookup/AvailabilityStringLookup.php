<?php

namespace Los\Lookup;

use Aptenex\Upp\Exception\CannotGenerateLosException;

class AvailabilityStringLookup implements AvailabilityLookupInterface
{

    /**
     * @var array
     */
    private $availabilityMap;

    /**
     * @param \DateTime $startingDate
     * @param string $availabilityString
     *
     * @throws CannotGenerateLosException
     */
    public function __construct(\DateTime $startingDate, string $availabilityString)
    {
        $this->parseAvailabilityString($startingDate, $availabilityString);
    }

    /**
     * @param string $date
     * @return bool
     */
    public function isAvailable(string $date): bool
    {
        if (!isset($this->availabilityMap[$date])) {
            return false; // If its not in the map then return false
        }

        return $this->availabilityMap[$date];
    }

    /**
     * @param \DateTime $startingDate
     * @param string $availabilityString
     *
     * @throws CannotGenerateLosException
     */
    private function parseAvailabilityString(\DateTime $startingDate, string $availabilityString)
    {
        if (empty($availabilityString)) {
            return;
        }

        $days = str_split($availabilityString);

        $startingDate = clone $startingDate;

        foreach($days as $index => $availabilityCharacter) {
            $this->availabilityMap[$startingDate->format('Y-m-d')] = ($availabilityCharacter === 'Y');

            try {
                $startingDate = $startingDate->add(new \DateInterval('P1D'));
            } catch (\Exception $e) {
                throw new CannotGenerateLosException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }

}