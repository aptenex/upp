<?php

namespace Aptenex\Upp\Parser\Structure\Condition;

use Aptenex\Upp\Parser\Structure\Condition;

/**
 * Class MultiDateCondition
 *
 * This is meant to be used for modifiers - not recommended for periods
 *
 * @package Aptenex\Upp\Parser\Structure\Condition
 */
class MultiDateCondition extends Condition
{

    const STRATEGY_MATCH = 'STRATEGY_MATCH';
    const STRATEGY_NOT_MATCH = 'STRATEGY_NOT_MATCH';

    /**
     * @var string
     */
    private $strategy = self::STRATEGY_MATCH;

    /**
     * @var array
     */
    private $dates = [];

    /**
     * @return string
     */
    public function getStrategy(): string
    {
        return $this->strategy;
    }

    /**
     * @param string $strategy
     */
    public function setStrategy(string $strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * @return array
     */
    public function getDates(): array
    {
        return $this->dates;
    }

    /**
     * @param array $dates
     */
    public function setDates(array $dates)
    {
        $this->dates = $dates;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return array_replace(parent::__toArray(), [
            'strategy'     => $this->getStrategy(),
            'dates'       => $this->getDates()
        ]);
    }

}