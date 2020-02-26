<?php

namespace Aptenex\Upp\Parser\Structure\Condition;

use Aptenex\Upp\Parser\Structure\Condition;

class GuestsCondition extends Condition
{

    /**
     * @var int
     */
    private $minimum;

    /**
     * @var int
     */
    private $maximum;

    /**
     * @return int|null
     */
    public function getMinimum()
    {
        return $this->minimum;
    }

    /**
     * @param int $minimum
     */
    public function setMinimum($minimum)
    {
        if ($minimum !== null) {
            $minimum = (int)$minimum;
        }

        $this->minimum = $minimum;
    }

    /**
     * @return int|null
     */
    public function getMaximum()
    {
        return $this->maximum;
    }

    /**
     * @param int $maximum
     */
    public function setMaximum($maximum)
    {
        if ($maximum !== null) {
            $maximum = (int)$maximum;
        }

        $this->maximum = $maximum;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return array_replace(parent::__toArray(), [
            'minimum' => $this->getMinimum(),
            'maximum' => $this->getMaximum()
        ]);
    }

}