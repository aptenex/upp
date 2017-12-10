<?php

namespace Aptenex\Upp\Helper;

use DateInterval;
use DatePeriod;
use DateTime;

class DateTools
{

    /**
     * @param DateTime $start
     * @param DateTime $end
     * @return DatePeriod|DateTime[]
     */
    public static function getDatesFromRange(\DateTime $start, \DateTime $end) {
        $interval = new DateInterval('P1D');

        $end = clone $end; // Don't want to modify the existing one
        $end->add($interval);

        return new DatePeriod($start, $interval, $end);
    }

    /**
     * @param DateTime $start
     * @param DateTime $end
     * @return DatePeriod|DateTime[]
     */
    public static function getNightsFromRange(\DateTime $start, \DateTime $end) {
        $nights = [];

        foreach(self::getDatesFromRange($start, $end) as $day) {
            $nights[] = $day;
        }

        array_pop($nights);

        return $nights;
    }

}