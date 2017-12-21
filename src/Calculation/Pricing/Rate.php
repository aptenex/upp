<?php

namespace Aptenex\Upp\Calculation\Pricing;

use Aptenex\Upp\Calculation\ControlItem\ControlItemInterface;
use Money\Money;

class Rate
{

    /**
     * @var ControlItemInterface
     */
    private $controlItem;

    /**
     * @var Money|null
     */
    private $damageDepositOverride;

    /**
     * @var Money|null
     */
    private $depositOverride;

    /**
     * @var array
     */
    private $strategyData;

    /**
     * @param ControlItemInterface $controlItem
     */
    public function __construct(ControlItemInterface $controlItem)
    {
        $this->controlItem = $controlItem;
    }

    /**
     * @return ControlItemInterface
     */
    public function getControlItem(): ControlItemInterface
    {
        return $this->controlItem;
    }

    /**
     * @return Money|null
     */
    public function getDamageDepositOverride()
    {
        return $this->damageDepositOverride;
    }

    /**
     * @param Money|null $damageDepositOverride
     */
    public function setDamageDepositOverride($damageDepositOverride)
    {
        $this->damageDepositOverride = $damageDepositOverride;
    }

    /**
     * @return bool
     */
    public function hasDamageDepositOverride()
    {
        return $this->damageDepositOverride instanceof Money;
    }

    /**
     * @return Money|null
     */
    public function getDepositOverride()
    {
        return $this->depositOverride;
    }

    /**
     * @param Money|null $depositOverride
     */
    public function setDepositOverride($depositOverride)
    {
        $this->depositOverride = $depositOverride;
    }

    /**
     * @return bool
     */
    public function hasDepositOverride()
    {
        return $this->depositOverride instanceof Money;
    }

    /**
     * @return array
     */
    public function getStrategyData()
    {
        return $this->strategyData;
    }

    /**
     * @param array $strategyData
     */
    public function setStrategyData($strategyData)
    {
        $this->strategyData = $strategyData;
    }

}