<?php

namespace Aptenex\Upp\Calculation;

use Aptenex\Upp\Util\MoneyUtils;
use Aptenex\Upp\Calculation\ControlItem\ControlItemInterface;
use Aptenex\Upp\Calculation\ControlItem\Modifier;
use Money\Money;

/**
 * This class represents a date - and contains the period and any modifiers it was matched for
 */
class Night
{

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var Money|null
     */
    private $cost = null;

    /**
     * @var ControlItemInterface
     */
    private $periodControlItem = null;

    /**
     * @var ControlItemInterface[]
     */
    private $modifierControlItems = [];

    /**
     * @param \DateTime $date
     * @param string $currency
     */
    public function __construct(\DateTime $date, $currency)
    {
        $this->date = new \DateTime($date->format("Y-m-d 00:00:00")); // Make sure its just the date
        $this->cost = \Aptenex\Upp\Util\MoneyUtils::newMoney(0, $currency);
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return null|Money
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param null|Money $cost
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
    }

    /**
     * @return ControlItemInterface
     */
    public function getPeriodControlItem()
    {
        return $this->periodControlItem;
    }

    /**
     * @return bool
     */
    public function hasPeriodControlItem()
    {
        return $this->getPeriodControlItem() instanceof ControlItemInterface;
    }

    /**
     * @param ControlItemInterface $periodControlItem
     */
    public function setPeriodControlItem($periodControlItem)
    {
        $this->periodControlItem = $periodControlItem;
    }

    /**
     * @return ControlItem\ControlItemInterface[]
     */
    public function getModifierControlItems()
    {
        return $this->modifierControlItems;
    }

    /**
     * @param ControlItem\ControlItemInterface[] $modifierControlItems
     */
    public function setModifierControlItems($modifierControlItems)
    {
        $this->modifierControlItems = $modifierControlItems;
    }

    /**
     * @param ControlItemInterface $controlItem
     */
    public function addModifierControlItem(ControlItemInterface $controlItem)
    {
        $this->modifierControlItems[] = $controlItem;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return [
            'date'      => $this->getDate()->format("Y-m-d"),
            'dayOfWeek' => strtolower($this->getDate()->format("l")),
            'cost'      => is_null($this->getCost()) ? null : MoneyUtils::getConvertedAmount($this->getCost()),
            'period'    => $this->getPeriodArray(),
            'modifiers' => $this->getModifiersArray()
        ];
    }

    /**
     * @return array|null
     */
    private function getPeriodArray()
    {
        if (!$this->hasPeriodControlItem()) {
            return null;
        }

        return [
            'description' => $this->getPeriodControlItem()->getControlItemConfig()->getDescription(),
            'rate'        => $this->getPeriodControlItem()->getControlItemConfig()->getRate()->__toArray()
        ];
    }

    /**
     * @return array
     */
    private function getModifiersArray()
    {
        $data = [];

        foreach ($this->getModifierControlItems() as $modifier) {
            /** @var \Aptenex\Upp\Parser\Structure\Modifier $config */
            $config = $modifier->getControlItemConfig();
            $data[] = [
                'type'        => $config->getType(),
                'description' => $config->getDescription(),
                'rate'        => $config->getRate()->__toArray()
            ];
        }

        return $data;
    }

}