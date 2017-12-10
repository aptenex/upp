<?php

namespace Aptenex\Upp\Parser\Structure\Condition;

use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\Structure\Condition;

class WeekdaysCondition extends Condition
{

    /**
     * @var string[]
     */
    private $weekdays = [];

    private static $weekdayList = [
          'monday',
          'tuesday',
          'wednesday',
          'thursday',
          'friday',
          'saturday',
          'sunday'
    ];

    /**
     * @return \string[]
     */
    public function getWeekdays()
    {
        return $this->weekdays;
    }

    /**
     * @param \string[] $weekdays
     */
    public function setWeekdays($weekdays)
    {
        if (!is_array($weekdays)) {
            $weekdays = [$weekdays];
        }

        $this->weekdays = ArrayAccess::filterByWhitelist($weekdays, self::$weekdayList);
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return array_replace(parent::__toArray(), [
            'weekdays' => $this->getWeekdays()
        ]);
    }

}