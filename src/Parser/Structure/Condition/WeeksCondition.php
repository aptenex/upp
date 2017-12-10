<?php

namespace Aptenex\Upp\Parser\Structure\Condition;

use Aptenex\Upp\Parser\Structure\Condition;

class WeeksCondition extends Condition
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
     * @return int
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
        if (!is_null($minimum)) {
            $minimum = (int) $minimum;
        }

        $this->minimum = $minimum;
    }

    /**
     * @return int
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
        if (!is_null($maximum)) {
            $maximum = (int) $maximum;
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