<?php

namespace Aptenex\Upp\Calculation;

use Aptenex\Upp\Util\MoneyUtils;
use Aptenex\Upp\Calculation\ControlItem\ControlItemInterface;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Parser\Structure\SplitMethod;
use Money\Money;

class AdjustmentAmount
{

    const TYPE_TAX = 'tax';
    const TYPE_EXTRA = 'extra';
    const TYPE_MODIFIER = 'modifier';
    const TYPE_DAMAGE_DEPOSIT = 'damage_deposit';

    const PRICE_GROUP_TOTAL = 'total';
    const PRICE_GROUP_BASE = 'base';
    const PRICE_GROUP_BASE_NON_TAXABLE = 'base_non_taxable';
    const PRICE_GROUP_NONE = 'none';
    const PRICE_GROUP_ARRIVAL = 'on_arrival';

    /**
     * This special group causes the adjustment to be added to the base price acting as total.
     * If the group is total and set to hidden then it will be changed to hidden_on_base.
     *
     * This allows us to hide adjustments and keep the base + adjustments + damage deposit = total,
     * otherwise the total does not add up.
     */
    const PRICE_GROUP_HIDDEN_ON_BASE = 'hidden_on_base';

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $priceGroup;

    /**
     * @var string
     */
    private $splitMethod = SplitMethod::ON_TOTAL;

    /**
     * @var Money
     */
    private $amount;

    /**
     * @var string
     */
    private $operand;

    /**
     * @var string
     */
    private $description;

    /**
     * To be used for various matching algorithm's such as extra matching for taxes
     *
     * @var string
     */
    private $identifier;

    /**
     * @var boolean
     */
    private $hidden;

    /**
     * @var ControlItemInterface|null
     */
    private $controlItem;

    /**
     * Whether or not this adjustment is just a 'note', notes do not affect price
     *
     * @var bool
     */
    private $noteOnly = false;

    /**
     * @param Money $amount
     * @param         $identifier
     * @param string $description
     * @param string $operand
     * @param string $type
     * @param string $priceGroup
     * @param string $splitMethod
     * @param boolean $hidden
     * @param ControlItemInterface|null $controlItem
     */
    public function __construct(Money $amount, $identifier, $description, $operand, $type, $priceGroup, $splitMethod, $hidden = true, ControlItemInterface $controlItem = null)
    {
        $this->amount = $amount;
        $this->identifier = $identifier;
        $this->description = $description;
        $this->operand = $operand;
        $this->type = $type;
        $this->priceGroup = $priceGroup;
        $this->splitMethod = $splitMethod;
        $this->hidden = $hidden;
        $this->controlItem = $controlItem;

        $this->performChecks();
    }

    private function performChecks()
    {
        if ($this->hidden && $this->priceGroup === self::PRICE_GROUP_TOTAL) {
            $this->priceGroup = self::PRICE_GROUP_HIDDEN_ON_BASE;
        } else if ($this->hidden && $this->priceGroup === self::PRICE_GROUP_BASE) {
            $this->priceGroup = self::PRICE_GROUP_HIDDEN_ON_BASE;
        }

        if ($this->splitMethod === 'ON_ARRIVAL') {
            $this->noteOnly = true;
            $this->operand = Operand::OP_NONE;
            $this->priceGroup = self::PRICE_GROUP_ARRIVAL;
        }
    }
    
    

    /**
     * @return Money
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getOperand()
    {
        return $this->operand;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return boolean
     */
    public function isHidden()
    {
        return $this->hidden;
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
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getPriceGroup()
    {
        return $this->priceGroup;
    }

    /**
     * @return string
     */
    public function getSplitMethod()
    {
        return $this->splitMethod;
    }

    /**
     * @return ControlItemInterface|null
     */
    public function getControlItem()
    {
        return $this->controlItem;
    }

    /**
     * @return bool
     */
    public function hasControlItem()
    {
        return $this->controlItem instanceof ControlItemInterface;
    }

    /**
     * @return bool
     */
    public function isNoteOnly(): bool
    {
        return $this->noteOnly;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return [
            'type'               => $this->getType(),
            'priceGroup'         => $this->getPriceGroup(),
            'amount'             => MoneyUtils::getConvertedAmount($this->getAmount()),
            'hidden'             => $this->isHidden(),
            'identifier'         => $this->getIdentifier(),
            'description'        => $this->getDescription(),
            'calculationOperand' => $this->getOperand(),
            'guestSplitMethod'        => $this->getSplitMethod(),
        ];
    }
	
	/**
	 * @param Money $amount
	 */
	public function setAmount(Money $amount)
	{
		$this->amount = $amount;
	}
	
}