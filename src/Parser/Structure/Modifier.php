<?php

namespace Aptenex\Upp\Parser\Structure;

class Modifier extends AbstractControlItem implements ControlItemInterface
{

    const TYPE_TAX = 'tax';
    const TYPE_BOOKING_FEE = 'booking_fee';
    const TYPE_MODIFIER = 'modifier';
    const TYPE_CLEANING = 'cleaning';
    const TYPE_CARD_FEE = 'card_fee';
    const TYPE_TOURISM_TAX = 'tourism_tax';
    const TYPE_EXTRA_GUEST_FEE = 'extra_guest_fee';
    const TYPE_BASE_PRICE_FEE = 'base_price_fee';

    public static $basePriceModifierTypes = [
        self::TYPE_EXTRA_GUEST_FEE,
        self::TYPE_BASE_PRICE_FEE
    ];

    /**
     * @var string
     */
    protected $type = self::TYPE_MODIFIER;

    /**
     * @var string
     */
    protected $splitMethod = SplitMethod::ON_TOTAL;

    /**
     * @var bool
     */
    protected $hidden = false;

    /**
     * @var bool
     */
    protected $mergeBasePrice = false;

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
    public function getSplitMethod()
    {
        return $this->splitMethod;
    }

    /**
     * @param string $splitMethod
     */
    public function setSplitMethod($splitMethod)
    {
        $this->splitMethod = $splitMethod;
    }

    /**
     * @return boolean
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * @param boolean $hidden
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * @return bool
     */
    public function isMergeBasePrice()
    {
        return $this->mergeBasePrice;
    }

    /**
     * @param bool $mergeBasePrice
     */
    public function setMergeBasePrice($mergeBasePrice)
    {
        $this->mergeBasePrice = $mergeBasePrice;
    }

    /**
     * @return bool
     */
    public function isBasePriceModifierType(): bool
    {
        return in_array($this->getType(), self::$basePriceModifierTypes, true);
    }

    /**
     * @return array
     */
    public function __toArray(): array
    {
        return array_replace(parent::__toArray(), [
            'type'           => $this->getType(),
            'splitMethod'    => $this->getSplitMethod(),
            'hidden'         => $this->isHidden(),
            'mergeBasePrice' => $this->isMergeBasePrice()
        ]);
    }

}