<?php

namespace Aptenex\Upp\Los\Lookup;

use Aptenex\Upp\Exception\CannotGenerateLosException;

class ChangeoverStringLookup implements ChangeoverLookupInterface
{

    public const ARRIVAL_ONLY = 1;
    public const DEPARTURE_ONLY = 2;
    public const ARRIVAL_OR_DEPARTURE = 3;
    public const NO_ARRIVAL_OR_DEPARTURE = 0;

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
    public function __construct(\DateTime $startingDate, $changeoverString, $changeoverDefault)
    {
        $this->changeoverDefault = (int) $changeoverDefault;

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
     * @return int
     */
    private function getChangeoverValue(string $date): int
    {
        return (int) ($this->changeoverMap[$date] ?? $this->changeoverDefault);
    }

    /**
     * @param \DateTime $startingDate
     * @param string $changeoverString
     *
     * @throws CannotGenerateLosException
     */
    private function parseChangeoverString(\DateTime $startingDate, $changeoverString): void
    {
        if (empty($changeoverString)) {
            $this->changeoverMap = [];

            return;
        }

        $days = str_split($changeoverString);

        $startingDate = clone $startingDate;

        foreach($days as $index => $changeoverInt) {
            $this->changeoverMap[$startingDate->format('Y-m-d')] = (int) $changeoverInt;

            try {
                $startingDate = $startingDate->add(new \DateInterval('P1D'));
            } catch (\Exception $e) {
                throw new CannotGenerateLosException($e->getMessage(), $e->getCode(), $e, func_get_args());
            }
        }
    }

}