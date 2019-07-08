<?php

namespace Aptenex\Upp\Los\Lookup;

use Aptenex\Upp\Exception\CannotGenerateLosException;

class ChangeoverStringLookup implements ChangeoverLookupInterface
{

    const ARRIVAL_ONLY = 1;
    const DEPARTURE_ONLY = 2;
    const ARRIVAL_OR_DEPARTURE = 3;
    const NO_ARRIVAL_OR_DEPARTURE = 0;

    /**
     * @var array
     */
    private $changeoverMap;

    /**
     * @var string
     */
    private $changeoverDefault;

    /**
     * @param \DateTime $startingDate
     * @param string $changeoverString
     * @param string $changeoverDefault
     *
     * @throws CannotGenerateLosException
     */
    public function __construct(\DateTime $startingDate, string $changeoverString, string $changeoverDefault)
    {
        $this->changeoverDefault = $changeoverDefault;

        $this->parseChangeoverString($startingDate, $changeoverString);
    }

    /**
     * @param string $date
     * @return bool
     */
    public function canArrive(string $date): bool
    {
        $changeover = $this->getChangeoverValue($date);

        return $changeover === self::ARRIVAL_ONLY || $changeover === self::ARRIVAL_OR_DEPARTURE;
    }

    /**
     * @param string $date
     * @return bool
     */
    public function canDepart(string $date): bool
    {
        $changeover = $this->getChangeoverValue($date);

        return $changeover === self::DEPARTURE_ONLY || $changeover === self::ARRIVAL_OR_DEPARTURE;
    }

    /**
     * @param string $date
     * @return bool
     */
    public function canArriveOrDepart(string $date): bool
    {
        return $this->canArrive($date) || $this->canDepart($date);
    }

    /**
     * @param string $date
     * @return bool
     */
    public function canArriveAndDepart(string $date): bool
    {
        return $this->canArrive($date) && $this->canDepart($date);
    }

    /**
     * @param string $date
     *
     * @return string
     */
    private function getChangeoverValue(string $date): string
    {
        return $this->changeoverMap[$date] ?? $this->changeoverDefault;
    }

    /**
     * @param \DateTime $startingDate
     * @param string $changeoverString
     *
     * @throws CannotGenerateLosException
     */
    private function parseChangeoverString(\DateTime $startingDate, string $changeoverString)
    {
        if (empty($changeoverString)) {
            $this->changeoverMap = [];
        }

        $days = str_split($changeoverString);

        $startingDate = clone $startingDate;

        foreach($days as $index => $changeoverInt) {
            $this->changeoverMap[$startingDate->format('Y-m-d')] = (int) $changeoverInt;

            try {
                $startingDate = $startingDate->add(new \DateInterval('P1D'));
            } catch (\Exception $e) {
                throw new CannotGenerateLosException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }

}