<?php

namespace Aptenex\Upp\Los;

class LosRecords
{

    /**
     * @var string
     */
    private $currency;

    /**
     * @var Metrics
     */
    private $metrics;

    /**
     * @var array
     */
    private $records;
    
    /**
     * Debug is an array of any values that take a
     * what can be contained in this.
     * @var array
     */
    private $debug;
    
    /**
     * @var LosOptions|null
     */
    private $buildOptions;

    public function __construct(string $currency, array $records = [])
    {
        $this->currency = $currency;
        $this->records = $records;
        $this->metrics = new Metrics();
        $this->debug = [];
    }

    /**
     * @param string $currency
     * @param array $records
     * @return LosRecords
     */
    public static function makeFromExisting(string $currency, array $records): LosRecords
    {
        return new LosRecords($currency, $records);
    }

    /**
     * We store min and max as the rate could be exactly the same regardless of the min/max.
     * Also we need to convert into different formats so storing it this way is helpful
     *
     * @param string $date
     * @param int $guest
     * @param array $rates
     */
    public function addLineEntry(string $date, int $guest, array $rates)
    {
        if (!isset($this->records[$date])) {
            $this->records[$date] = [];
        }

        $this->records[$date][] = [
            'date' => $date,
			'currency' => $this->currency,
            'guest' => $guest,
            'rates' => $rates,
            'rateHash' => sha1(implode(',', $rates))
        ];
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return array
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    /**
     * @return Metrics
     */
    public function getMetrics(): Metrics
    {
        return $this->metrics;
    }
    
    
    /**
     * @param iterable $debug
     */
    public function setDebug(iterable $debug): void
    {
        $this->debug = $debug;
    }
    
    public function getDebug(): iterable
    {
        return $this->debug;
    }
    
    /**
     * @return LosOptions|null
     */
    public function getBuildOptions(): ?LosOptions
    {
        return $this->buildOptions;
    }
    
    /**
     * @param LosOptions|null $buildOptions
     */
    public function setBuildOptions(?LosOptions $buildOptions): void
    {
        $this->buildOptions = $buildOptions;
    }
    
    

    
    
}