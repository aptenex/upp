<?php

namespace Aptenex\Upp\Parser\Structure;

use Aptenex\Upp\Parser\Structure\DaysOfWeek\DaysOfWeek;
use Doctrine\Common\Annotations\Annotation\Required;

class Rate
{

    public const METHOD_FIXED      = 'fixed';
    public const METHOD_PERCENTAGE = 'percentage';
    
    

    public const TYPE_WEEKLY     = 'weekly';
    public const TYPE_NIGHTLY    = 'nightly';
    public const TYPE_MONTHLY    = 'monthly';
    public const TYPE_ADJUSTMENT = 'adjustment';

    /**
     * @Required()
     *
     * @var string
     */
    protected $type;

    /**
     * @Required()
     *
     * @var string
     */
    protected $calculationMethod = self::METHOD_FIXED;

    /**
     * @var number
     */
    protected $amount;

    /**
     * @var number
     */
    protected $damageDeposit;

    /**
     * @var RateStrategy
     */
    protected $strategy;

    /**
     * @var DaysOfWeek|null
     */
    protected $daysOfWeek;

    /**
     * @var string
     */
    protected $calculationOperator = Operator::OP_ADDITION;

    /**
     *
     * @var bool
     */
    protected $taxable = false;

    /**
     * Array of Tax UUID's - blank to apply to all taxes if taxable is true
     *
     * @var string[]
     */
    protected $applicableTaxes = [];

    /**
     *
     * @var bool
     */
    protected $basePriceOnly = false;

    /**
     * @return string
     */
    public function getCalculationOperator()
    {
        return $this->calculationOperator;
    }

    /**
     * @param string $calculationOperator
     */
    public function setCalculationOperator($calculationOperator)
    {
        if (!in_array($calculationOperator, Operator::getMathList(), true)) {
            $calculationOperator = Operator::OP_ADDITION;
        }

        $this->calculationOperator = $calculationOperator;
    }

    /**
     * @deprecated Use getCalculationOperator()
     *
     * @return string
     */
    public function getCalculationOperand()
    {
        return $this->getCalculationOperator();
    }

    /**
     * @deprecated Use setCalculationOperator()
     *
     * @param string $calculationOperator
     */
    public function setCalculationOperand($calculationOperator)
    {
        $this->setCalculationOperator($calculationOperator);
    }

    /**
     * @return boolean
     */
    public function isBasePriceOnly()
    {
        return $this->basePriceOnly;
    }

    /**
     * @param boolean $basePriceOnly
     */
    public function setBasePriceOnly($basePriceOnly)
    {
        $this->basePriceOnly = (bool) $basePriceOnly;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getCalculationMethod()
    {
        return $this->calculationMethod;
    }

    /**
     * @param string $calculationMethod
     */
    public function setCalculationMethod($calculationMethod)
    {
        $this->calculationMethod = $calculationMethod;
    }

    /**
     * @return number
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return number|string
     */
    public function getRoughNightlyAmount()
    {
        if ($this->getType() === Rate::TYPE_WEEKLY) {
            return number_format($this->getAmount() / 7, 2, '.', '');
        } else if ($this->getType() === Rate::TYPE_MONTHLY) {
            return number_format($this->getAmount() / 30, 2, '.', '');
        }

        return $this->getAmount();
    }

    /**
     * @param number $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return RateStrategy
     */
    public function getStrategy()
    {
        return $this->strategy;
    }

    /**
     * @param RateStrategy $strategy
     */
    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * @return number
     */
    public function getDamageDeposit()
    {
        return $this->damageDeposit;
    }

    /**
     * @return bool
     */
    public function hasDamageDeposit()
    {
        return !empty($this->getDamageDeposit()) && is_numeric($this->getDamageDeposit());
    }

    /**
     * @param number $damageDeposit
     */
    public function setDamageDeposit($damageDeposit)
    {
        $this->damageDeposit = $damageDeposit;
    }

    /**
     * @return bool
     */
    public function isTaxable(): bool
    {
        return $this->taxable;
    }

    /**
     * @param bool $taxable
     */
    public function setTaxable(bool $taxable)
    {
        $this->taxable = $taxable;
    }

    /**
     * @return \string[]
     */
    public function getApplicableTaxes(): array
    {
        return $this->applicableTaxes;
    }

    /**
     * @param \string[] $applicableTaxes
     */
    public function setApplicableTaxes(array $applicableTaxes)
    {
        $this->applicableTaxes = $applicableTaxes;
    }

    /**
     * @return DaysOfWeek
     */
    public function getDaysOfWeek(): DaysOfWeek
    {
        return $this->daysOfWeek;
    }

    /**
     * @param DaysOfWeek|null $daysOfWeek
     */
    public function setDaysOfWeek(?DaysOfWeek $daysOfWeek): void
    {
        $this->daysOfWeek = $daysOfWeek;
    }

    /**
     * @return bool
     */
    public function hasDaysOfWeek(): bool
    {
        return $this->daysOfWeek instanceof DaysOfWeek;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return [
            'type'                => $this->getType(),
            'amount'              => $this->getAmount(),
            'taxable'             => $this->isTaxable(),
            'damageDeposit'       => $this->getDamageDeposit(),
            'basePriceOnly'       => $this->isBasePriceOnly(),
            'applicableTaxes'     => $this->getApplicableTaxes(),
            'calculationMethod'   => $this->getCalculationMethod(),
            'calculationOperand'  => $this->getCalculationOperand(),
            'calculationOperator' => $this->getCalculationOperator(),
            'daysOfWeek'          => $this->hasDaysOfWeek() ? $this->getDaysOfWeek()->__toArray() : null,
            'strategy'            => $this->getStrategy() ? $this->getStrategy()->__toArray() : null,
        ];
    }

}