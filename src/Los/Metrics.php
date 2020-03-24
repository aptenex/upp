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
     * @return Metrics
     */
    public function setTimesRan(int $timesRan): Metrics
    {
        $this->timesRan = $timesRan;
        return $this;
    }
    
    /**
     * @param int $maxPotentialRuns
     * @return Metrics
     */
    public function setMaxPotentialRuns(int $maxPotentialRuns): Metrics
    {
        $this->maxPotentialRuns = $maxPotentialRuns;
        return $this;
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
    
    public function startTiming(): void
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
     * @return Metrics
     */
    public function setTotalDuration($totalDuration): Metrics
    {
        $this->totalDuration = $totalDuration;
        return $this;
        
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
     * @return Metrics
     */
    public function setLongestDuration($longestDuration): self
    {
        $this->longestDuration = $longestDuration;
        return $this;
    }
    
    public function __toArray(): array
    {
        return [
            'timesRan' =>  $this->getTimesRan(),
            'efficiencyPercentage' => $this->getEfficiencyPercentage(),
            'maxPotentialRuns' => $this->getMaxPotentialRuns(),
            'totalDuration' => $this->getTotalDuration(),
            'longestDuration' => $this->getLongestDuration()
        ];
    }
    
    public static function fromArray($arr): Metrics
    {
        return  (new self())->setLongestDuration($arr['longestDuration'])
                            ->setTimesRan($arr['timesRan'])
                            ->setMaxPotentialRuns($arr['maxPotentialRuns'])
                            ->setTotalDuration($arr['totalDuration'])
                            ->setLongestDuration($arr['longestDuration']);
        
    }
    
    
}

