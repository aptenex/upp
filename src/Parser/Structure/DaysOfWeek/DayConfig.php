<?php

namespace Aptenex\Upp\Parser\Structure\DaysOfWeek;

use Aptenex\Upp\Exception\InvalidPricingConfigException;

class DayConfig
{

    public const ARRIVAL_ONLY = 'ARRIVAL_ONLY';
    public const DEPARTURE_ONLY = 'DEPARTURE_ONLY';
    public const ARRIVAL_OR_DEPARTURE = 'ARRIVAL_OR_DEPARTURE';
    public const NO_ARRIVAL_OR_DEPARTURE = 'NONE';

    public const CHANGEOVER_LIST = [
        self::ARRIVAL_ONLY,
        self::DEPARTURE_ONLY,
        self::ARRIVAL_OR_DEPARTURE,
        self::NO_ARRIVAL_OR_DEPARTURE
    ];

    public const WEEKDAY_LIST = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday'
    ];

    /**
     * @var string|null
     */
    private $day;

    /**
     * @var float|null
     */
    private $amount;

    /**
     * @var int|null
     */
    private $minimumNights;

    /**
     * @var string|null
     */
    private $changeover = DayConfig::ARRIVAL_OR_DEPARTURE;

    /**
     * @return null|string
     */
    public function getDay(): ?string
    {
        return $this->day;
    }

    /**
     * @param null|string $day
     *
     * @throws InvalidPricingConfigException
     */
    public function setDay(?string $day): void
    {
        if (!\in_array($day, self::WEEKDAY_LIST, true)) {
            throw new InvalidPricingConfigException('Invalid day being set on DayConfig (' . $day . ')');
        }

        $this->day = $day;
    }

    /**
     * @return float|null
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * @param float|null $amount
     */
    public function setAmount(?float $amount): void
    {
        $this->amount = $amount;
    }

    public function hasAmount(): bool
    {
        return $this->amount > 0;
    }

    /**
     * @return int|null
     */
    public function getMinimumNights(): ?int
    {
        return $this->minimumNights;
    }

    /**
     * @return bool
     */
    public function hasMinimumNights(): bool
    {
        return !empty($this->minimumNights) && $this->minimumNights !== 0;
    }

    /**
     * @param int|null $minimumNights
     */
    public function setMinimumNights(?int $minimumNights): void
    {
        $this->minimumNights = $minimumNights;
    }

    /**
     * @return null|string
     */
    public function getChangeover(): ?string
    {
        return $this->changeover;
    }

    /**
     * @param null|string $changeover
     *
     * @throws InvalidPricingConfigException
     */
    public function setChangeover(?string $changeover): void
    {
        if (!\in_array($changeover, self::CHANGEOVER_LIST, true)) {
            throw new InvalidPricingConfigException('Invalid changeover being set on DayConfig (' . $changeover . ')');
        }

        $this->changeover = $changeover;
    }

    public function __toArray(): array
    {
        return [
            'day'           => $this->getDay(),
            'amount'        => $this->getAmount(),
            'minimumNights' => $this->getMinimumNights(),
            'changeover'    => $this->getChangeover()
        ];
    }

}