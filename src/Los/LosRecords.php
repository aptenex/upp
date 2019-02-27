<?php

namespace Los;

use Los\Transformer\SimpleArrayRecordTransformer;

class LosRecords
{

    /**
     * @var array
     */
    private $records = [];

    /**
     * @var int
     */
    private $timesUppRan = 0;

    /**
     * @var int
     */
    private $maxAmountOfPotentialUppRuns = 0;

    /**
     * We store min and max as the rate could be exactly the same regardless of the min/max.
     * Also we need to convert into different formats so storing it this way is helpful
     *
     * @param string $currency
     * @param string $date
     * @param int $minGuest
     * @param int $maxGuest
     * @param array $rates
     */
    public function addLineEntry(string $currency, string $date, int $minGuest, int $maxGuest, array $rates)
    {
        if (!isset($this->records[$currency])) {
            $this->records[$currency] = [];
        }

        if (!isset($this->records[$currency][$date])) {
            $this->records[$currency][$date] = [];
        }

        $this->records[$currency][$date][] = [
            'date' => $date,
            'minGuest' => $minGuest,
            'maxGuest' => $maxGuest,
            'rates' => $rates
        ];
    }

    /**
     * @return array
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    /**
     * @return array
     */
    public function toSimpleArrayRecord(): array
    {
        return (new SimpleArrayRecordTransformer())->transform($this);
    }

    /**
     * @return int
     */
    public function getTimesUppRan(): int
    {
        return $this->timesUppRan;
    }

    /**
     * @return int
     */
    public function getMaxAmountOfPotentialUppRuns(): int
    {
        return $this->maxAmountOfPotentialUppRuns;
    }

    /**
     * @param int $timesUppRan
     */
    public function setTimesUppRan(int $timesUppRan)
    {
        $this->timesUppRan = $timesUppRan;
    }

    /**
     * @param int $maxAmountOfPotentialUppRuns
     */
    public function setMaxAmountOfPotentialUppRuns(int $maxAmountOfPotentialUppRuns)
    {
        $this->maxAmountOfPotentialUppRuns = $maxAmountOfPotentialUppRuns;
    }

    /**
     * @return string
     */
    public function getRunDataToString(): string
    {
        return sprintf(
            'Times Ran: %s (Maximum: %s) Efficiency: %s%%',
            $this->getTimesUppRan(),
            $this->getMaxAmountOfPotentialUppRuns(),
            round(100 - (($this->getTimesUppRan() / $this->getMaxAmountOfPotentialUppRuns()) * 100), 3)
        );
    }

}