<?php

namespace Aptenex\Upp\Los\Lookup;

use Aptenex\Upp\Exception\CannotGenerateLosException;

class AvailabilityStringLookup implements AvailabilityLookupInterface
{

    /**
     * @var array
     */
    private $availabilityMap;

    /**
     * @var string
     */
    private $availabilityDefault;

    /**
     * @param \DateTime $startingDate
     * @param string $availabilityString
     * @param $availabilityDefault
     *
     * @throws CannotGenerateLosException
     */
    public function __construct(\DateTime $startingDate, $availabilityString, $availabilityDefault)
    {
        $this->availabilityDefault = $availabilityDefault;

        $this->parseAvailabilityString($startingDate, $availabilityString);
    }

    /**
     * @param string $date
     * @return bool
     */
    public function isAvailable(string $date): bool
    {
        return $this->availabilityMap[$date] ?? $this->availabilityDefault;
    }

    /**
     * @param \DateTime $startingDate
     * @param string $availabilityString
     *
     * @throws CannotGenerateLosException
     */
    private function parseAvailabilityString(\DateTime $startingDate, string $availabilityString): void
    {
        if (empty($availabilityString)) {
            $this->availabilityMap = [];

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