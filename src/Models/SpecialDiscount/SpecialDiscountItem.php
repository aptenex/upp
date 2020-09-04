<?php


namespace Aptenex\Upp\Models\SpecialDiscount;


use Aptenex\Upp\Models\DateRange;

class SpecialDiscountItem
{

    public const OPERAND_TYPE_FIXED = 'FIXED';
    public const OPERAND_TYPE_PERCENTAGE = 'PERCENTAGE';

    public const DISCOUNT_CLASSIFICATION_EARLY_BIRD = 'EARLY_BIRD';
    public const DISCOUNT_CLASSIFICATION_LAST_MINUTE = 'LAST_MINUTE';

    public const DISCOUNT_CATEGORY_DAYS_BEFORE_ARRIVAL = 'DAYS_BEFORE_ARRIVAL';

    /**
     * @var string|null
     */
    protected $classification;

    /**
     * @var string|null
     */
    protected $type;

    /**
     * @var string|null
     */
    protected $category;

    /**
     * @var number|float|int|null
     */
    protected $amount;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var DateRange[]
     */
    protected $dateRanges = [];

    /**
     * @var int
     */
    protected $minimumDaysBeforeArrival;

    /**
     * @var int
     */
    protected $maximumDaysBeforeArrival;

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @param string|null $category
     */
    public function setCategory(?string $category): void
    {
        $this->category = $category;
    }

    /**
     * @return float|int|number|null
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float|int|number|null $amount
     */
    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return DateRange[]
     */
    public function getDateRanges(): array
    {
        return $this->dateRanges;
    }

    /**
     * @param DateRange[] $dateRanges
     */
    public function setDateRanges(array $dateRanges): void
    {
        $this->dateRanges = $dateRanges;
    }

    /**
     * @return int
     */
    public function getMinimumDaysBeforeArrival(): ?int
    {
        return $this->minimumDaysBeforeArrival;
    }

    /**
     * @param int $minimumDaysBeforeArrival
     */
    public function setMinimumDaysBeforeArrival(?int $minimumDaysBeforeArrival): void
    {
        $this->minimumDaysBeforeArrival = $minimumDaysBeforeArrival;
    }

    /**
     * @return int
     */
    public function getMaximumDaysBeforeArrival(): ?int
    {
        return $this->maximumDaysBeforeArrival;
    }

    /**
     * @param int $maximumDaysBeforeArrival
     */
    public function setMaximumDaysBeforeArrival(?int $maximumDaysBeforeArrival): void
    {
        $this->maximumDaysBeforeArrival = $maximumDaysBeforeArrival;
    }

    /**
     * @return string|null
     */
    public function getClassification(): ?string
    {
        return $this->classification;
    }

    /**
     * @param string|null $classification
     */
    public function setClassification(?string $classification): void
    {
        $this->classification = $classification;
    }

    public function __toArray()
    {
        $data = [
            'name' => $this->getName(),
            'amount' => $this->getAmount(),
            'type' => $this->getType(),
            'category' => $this->getCategory(),
            'classification' => $this->getClassification(),
            'dateRanges' =>  array_map(function ($item) {
                return [
                    'startDate' => $item->getStartDate()->format('Y-m-d'),
                    'endDate' => $item->getEndDate()->format('Y-m-d')
                ];
            }, $this->getDateRanges())
        ];

        if ($this->getCategory() === self::DISCOUNT_CATEGORY_DAYS_BEFORE_ARRIVAL) {
            $data['minimumDaysBeforeArrival'] = $this->getMinimumDaysBeforeArrival();
            $data['maximumDaysBeforeArrival'] = $this->getMaximumDaysBeforeArrival();
        }

        return $data;
    }

}