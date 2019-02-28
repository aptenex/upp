<?php

namespace Los;

use Los\Transformer\ArrayRecordTransformer;

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
     * @var mixed
     */
    private $startTime;

    /**
     * @var mixed
     */
    private $finishTime;

    /**
     * @var mixed
     */
    private $totalTime;

    /**
     * We store min and max as the rate could be exactly the same regardless of the min/max.
     * Also we need to convert into different formats so storing it this way is helpful
     *
     * @param string $currency
     * @param string $date
     * @param int $guest
     * @param array $rates
     */
    public function addLineEntry(string $currency, string $date, int $guest, array $rates)
    {
        if (!isset($this->records[$currency])) {
            $this->records[$currency] = [];
        }

        if (!isset($this->records[$currency][$date])) {
            $this->records[$currency][$date] = [];
        }

        $this->records[$currency][$date][] = [
            'date' => $date,
            'guest' => $guest,
            'rates' => $rates,
            'rateHash' => sha1(implode(',', $rates))
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
        return (new ArrayRecordTransformer())->transform($this);
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
     * @return float
     */
    public function getEfficiencyPercentage(): float
    {
        return round(100 - (($this->getTimesUppRan() / $this->getMaxAmountOfPotentialUppRuns()) * 100), 3);
    }

    /**
     * @return string
     */
    public function getRunDataToString(): string
    {
        return sprintf(
            'Completed in %ss%sTimes Ran: %s (Maximum: %s)%sEfficiency: %s%%',
            round($this->totalTime / 1000, 2),
            PHP_EOL,
            $this->getTimesUppRan(),
            $this->getMaxAmountOfPotentialUppRuns(),
            PHP_EOL,
            $this->getEfficiencyPercentage()
        );
    }

    public function startTiming()
    {
        $execTime = microtime();
        $execTime = explode(" ", $execTime);
        $execTime = $execTime[1] + $execTime[0];
        $startTime = $execTime;

        $this->startTime = $startTime;
    }

    public function finishTiming()
    {
        $execTime = microtime();
        $execTime = explode(" ", $execTime);
        $execTime = $execTime[1] + $execTime[0];
        $this->finishTime = $execTime;

        $this->totalTime = ($this->finishTime - $this->startTime) * 1000; // Get Milliseconds

        return $this->totalTime;
    }

    /**
     * In milliseconds
     *
     * @return mixed
     */
    public function getTotalTime()
    {
        return $this->totalTime;
    }

}