<?php

namespace Aptenex\Upp\Calculation\Condition;

use Aptenex\Upp\Calculation\ControlItem\ControlItemInterface;

class MatchedDate
{

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var ControlItemInterface
     */
    private $controlItem;

    /**
     * @var Condition
     */
    private $condition;

    /**
     * @param \DateTime $date
     * @param ControlItemInterface $controlItem
     * @param Condition $condition
     */
    public function __construct(\DateTime $date, ControlItemInterface $controlItem, Condition $condition)
    {
        $this->date = $date;
        $this->controlItem = $controlItem;
        $this->condition = $condition;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return ControlItemInterface
     */
    public function getControlItem()
    {
        return $this->controlItem;
    }

    /**
     * @return Condition
     */
    public function getCondition()
    {
        return $this->condition;
    }

}