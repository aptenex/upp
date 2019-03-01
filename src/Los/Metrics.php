<?php

namespace Los;

class Metrics
{

    /**
     * @var int
     */
    private $timesRan = 0;

    /**
     * @var int
     */
    private $maxPotentialRuns = 0;

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
     * @return int
     */
    public function getTimesRan(): int
    {
        return $this->timesRan;
    }

    /**
     * @return int
     */
    public function getMaxPotentialRuns(): int
    {
        return $this->maxPotentialRuns;
    }

    /**
     * @param int $timesRan
     */
    public function setTimesRan(int $timesRan)
    {
        $this->timesRan = $timesRan;
    }

    /**
     * @param int $maxPotentialRuns
     */
    public function setMaxPotentialRuns(int $maxPotentialRuns)
    {
        $this->maxPotentialRuns = $maxPotentialRuns;
    }

    /**
     * @return float
     */
    public function getEfficiencyPercentage(): float
    {
        return round(100 - (($this->getTimesRan() / $this->getMaxPotentialRuns()) * 100), 3);
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
            $this->getTimesRan(),
            $this->getMaxPotentialRuns(),
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
     * @param mixed $totalTime
     */
    public function setTotalTime($totalTime)
    {
        $this->totalTime = $totalTime;
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