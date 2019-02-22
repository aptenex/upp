<?php

namespace Aptenex\Upp\Parser\Structure;

use Aptenex\Upp\Util\ArrayUtils;
use Aptenex\Upp\Parser\Structure\Condition\DateCondition;
use Aptenex\Upp\Util\DateUtils;
use Symfony\Component\Validator\Constraints\NotBlank;

class CurrencyConfig
{

    /**
     * @NotBlank()
     *
     * @var string
     */
    private $currency;

    /**
     * @var Defaults
     */
    private $defaults;

    /**
     * @var Tax[]
     */
    private $taxes = [];

    /**
     * @var Period[]
     */
    private $periods = [];

    /**
     * @var Modifier[]
     */
    private $modifiers = [];

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = strtoupper(trim($currency));
    }

    /**
     * @return Period[]
     */
    public function getPeriods()
    {
        return $this->periods;
    }

    /**
     * @param Period $period
     */
    public function addPeriod(Period $period)
    {
        $this->periods[] = $period;
    }

    /**
     * @param Period[] $periods
     * @param bool $reorder
     */
    public function setPeriods($periods, $reorder = true)
    {
        // For now, we are not letting the user set the priority, we will determine the priority
        // based off if a date range is within a larger date range, meaning the nested range will
        // get priority as it makes sense

        if ($reorder) {
            $periods = DateUtils::reorderPeriods($periods);
        }

        $this->periods = $periods;
    }

    /**
     * @return Modifier[]
     */
    public function getModifiers()
    {
        return $this->modifiers;
    }

    /**
     * @param Modifier[] $modifiers
     */
    public function setModifiers($modifiers)
    {
        $this->modifiers = $modifiers;
    }

    /**
     * @return Defaults
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * @param Defaults $defaults
     */
    public function setDefaults($defaults)
    {
        $this->defaults = $defaults;
    }

    /**
     * @return Tax[]
     */
    public function getTaxes()
    {
        return $this->taxes;
    }

    /**
     * @param Tax[] $taxes
     */
    public function setTaxes($taxes)
    {
        $this->taxes = $taxes;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return [
            'currency' => $this->getCurrency(),
            'defaults' => $this->getDefaults()->__toArray(),
            'taxes' => ArrayUtils::convertStructureObjectToArray($this->getTaxes()),
            'periods' => ArrayUtils::convertStructureObjectToArray($this->getPeriods()),
            'modifiers' => ArrayUtils::convertStructureObjectToArray($this->getModifiers())
        ];
    }

}