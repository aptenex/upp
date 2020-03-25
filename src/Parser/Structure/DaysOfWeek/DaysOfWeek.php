<?php

namespace Aptenex\Upp\Parser\Structure\DaysOfWeek;

use Aptenex\Upp\Parser\Structure\Rate;

class DaysOfWeek
{

    /**
     * @var string|null
     */
    protected $calculationMethod = Rate::METHOD_FIXED;

    /**
     * @var DayConfig[]|null
     */
    protected $days = [];

    public function getArrivalChangeoverList(): array
    {
        $arrivals = [];

        foreach($this->getDays() as $day) {
            if (\in_array($day->getChangeover(), [DayConfig::ARRIVAL_OR_DEPARTURE, DayConfig::ARRIVAL_ONLY], true)) {
                $arrivals[] = $day->getDay();
            }
        }

        return $arrivals;
    }

    public function getDepartureChangeoverList(): array
    {
        $departures = [];

        foreach($this->getDays() as $day) {
            if (\in_array($day->getChangeover(), [DayConfig::ARRIVAL_OR_DEPARTURE, DayConfig::DEPARTURE_ONLY], true)) {
                $departures[] = $day->getDay();
            }
        }

        return $departures;
    }

    /**
     * @return null|string
     */
    public function getCalculationMethod(): ?string
    {
        return $this->calculationMethod;
    }

    /**
     * @param null|string $calculationMethod
     */
    public function setCalculationMethod(?string $calculationMethod): void
    {
        $this->calculationMethod = $calculationMethod;
    }

    /**
     * @return DayConfig[]|null
     */
    public function getDays(): ?array
    {
        return $this->days;
    }

    /**
     * @param DayConfig[]|null $days
     */
    public function setDays(?array $days): void
    {
        $this->days = $days;
    }

    /**
     * @param string $day
     *
     * @return null|DayConfig
     */
    public function getDayConfigByDay(string $day): ?DayConfig
    {
        return $this->days[\strtolower($day)] ?? null;
    }

    public function __toArray(): array
    {
        $days = [];

        foreach($this->getDays() as $day) {
            $days[$day->getDay()] = $day->__toArray();
        }

        return [
            'calculationMethod' => $this->getCalculationMethod(),
            'days' => $days
        ];
    }

}