<?php

namespace Aptenex\Upp\Parser\Structure;

class Modifier extends AbstractControlItem implements ControlItemInterface
{

    const TYPE_TAX = 'tax';
    const TYPE_BOOKING_FEE = 'booking_fee';
    const TYPE_MODIFIER = 'modifier';
    const TYPE_CLEANING = 'cleaning';

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
     * @return array
     */
    public function __toArray()
    {
        return array_replace(parent::__toArray(), [
            'type'        => $this->getType(),
            'splitMethod' => $this->getSplitMethod(),
            'hidden'      => $this->isHidden()
        ]);
    }

}