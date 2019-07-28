<?php

namespace Aptenex\Upp\Los;

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
    private $totalDuration;
    
    /**
     * The longest duration is only useful when we merge results. It will give the longest duration..
     * @var mixed
     */
    private $longestDuration;

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
        if($this->getMaxPotentialRuns() === 0){
            // It's a 100, cause we could never run it.
            return 100;
        }
        return round(100 - (($this->getTimesRan() / $this->getMaxPotentialRuns()) * 100), 3);
    }

    /**
     * @return string
     */
    public function getRunDataToString(): string
    {
        return sprintf(
            'Completed in %ss%sLongest Duration: %ss%sTimes Ran: %s (Maximum: %s)%sEfficiency: %s%%',
            round($this->totalDuration / 1000, 2),
            PHP_EOL,
            round($this->longestDuration / 1000, 2),
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

        $this->totalDuration = ($this->finishTime - $this->startTime) * 1000; // Get Milliseconds
        $this->longestDuration = $this->totalDuration; // These are effectively the same when set via start/finish timing

        return $this->totalDuration;
    }

    /**
     * @param mixed $totalDuration
     */
    public function setTotalDuration($totalDuration)
    {
        $this->totalDuration = $totalDuration;
    }

    /**
     * In milliseconds
     *
     * @return mixed
     */
    public function getTotalDuration()
    {
        return $this->totalDuration;
    
    
    }
    
    /**
     * @return mixed
     */
    public function getLongestDuration()
    {
        return $this->longestDuration;
    }
    
    /**
     * @param mixed $longestDuration
     */
    public function setLongestDuration($longestDuration): void
    {
        $this->longestDuration = $longestDuration;
    }


}

