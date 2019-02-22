<?php

namespace Aptenex\Upp\Util;

use DateTime;
use Aptenex\Upp\Parser\Structure\Period;
use Aptenex\Upp\Util\DateTimeAgo\DateTimeAgo;
use Aptenex\Upp\Parser\Structure\Condition\DateCondition;
use Spatie\Period\Boundaries;
use Spatie\Period\Precision;

class DateUtils
{

    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;
    const SUNDAY = 7;

    /**
     * @return \DateTime
     */
    public static function newD(): DateTime
    {
        return new \DateTime(date('Y-m-d'), new \DateTimeZone('UTC'));
    }

    /**
     * @return \DateTime
     */
    public static function newDt(): DateTime
    {
        return new \DateTime(date('Y-m-d H:i:s'), new \DateTimeZone('UTC'));
    }

    public static function reorderPeriods(array &$periods): array
    {
        /** @var Period[] $pp1 */
        $pp1 = ArrayUtils::cloneArray($periods);
        /** @var Period[] $pp2 */
        $pp2 = ArrayUtils::cloneArray($periods);
        foreach ($pp1 as $i1 => $p1) {
            /** @var DateCondition $dc1 */
            $dc1 = $p1->getDateCondition();

            if ($dc1 === null) {
                continue;
            }

            foreach ($pp2 as $i2 => $p2) {
                if ($p1->getId() === $p2->getId()) {
                    continue;
                }

                /** @var DateCondition $dc2 */
                $dc2 = $p2->getDateCondition();
                if ($dc2 === null) {
                    continue;
                }

                $dc1Start = new \DateTime($dc1->getStartDate());
                $dc1End = new \DateTime($dc1->getEndDate());
                $dc2Start = new \DateTime($dc2->getStartDate());
                $dc2End = new \DateTime($dc2->getEndDate());

                // Here we will check intersecting dates
                if ($dc2Start > $dc1Start && $dc1End > $dc2End) {
                    // This is a nested date
                    $periods[$i2]->setPriority($p2->getPriority() + 1);
                }
            }

        }

        // We need to sort this by the specified priority
        // Higher priority = first
        usort($periods, function ($a, $b) {

            /**
             * @var $a Period
             * @var $b Period
             */

            if ($a->getPriority() > $b->getPriority()) {
                return -1;
            }

            if ($a->getPriority() < $b->getPriority()) {
                return 1;
            }

            return 0;
        });

        return $periods;
    }

    /**
     * This will take a set of periods, and expand any dates/nested dates into a linear set as
     * best as possible. Partially nested dates will have the partial nested date take priority
     * for the start date and the same for the end date too.
     *
     * @param array $periods
     *
     * @return array
     */
    public static function expandPeriods(array $periods): array
    {
        // Loop through each period and determine whether any other period intersects with it
        // if it does intersect COMPLETELY then we need to split it into 3 periods. If it half intersects
        // then split it into two

        $newPeriodArray = [];
        $hasModified = false;

        // The method for expanding these periods is:
        // 1. Sort from longest to shortest into an array & take the total length of the period set
        // 2. Create an array of dates from start to end
        // 3. In the sorted period array expand each period into date => period spanning the whole length so some dates => null
        // 4. This is a 2-dimensional array, with the lowest index being the longest period which contains the expanded dates
        // 5. Loop through the array of dates, perform a sequential lookup (from high to low) through the indexes of the 2-dimensional period array
        // 6. Pick the first period that matches and assign it to that date - which will be the smallest period for that date which is most likely nested
        // The reason we do high to low, is that low to high we will always have to loop to the end to check if any period exists for that date
        // whereas going from high to low, means that we can stop immediately after one has been found as it was sorted in the reverse order prior

        foreach($periods as $period1) {
            $dc1 = $period1['conditions'][0];

            $sp1 = \Spatie\Period\Period::make(
                new \DateTime($dc1['startDate']),
                new \DateTime($dc1['endDate']),
                Precision::DAY,
                Boundaries::EXCLUDE_NONE
            );

            foreach($periods as $period2) {
                $dc2 = $period2['conditions'][0];

                if ($dc1['startDate'] === $dc2['startDate'] && $dc1['endDate'] === $dc2['endDate']) {
                    continue; // Skip as they are the same period
                }

                $sp2 = \Spatie\Period\Period::make(
                    new \DateTime($dc2['startDate']),
                    new \DateTime($dc2['endDate']),
                    Precision::DAY,
                    Boundaries::EXCLUDE_NONE
                );

                if ($sp1->overlapsWith($sp2)) {

                    // Check that sp2 is completely contained within sp1, otherwise we ignore
                    if ($sp2->getStart() < $sp1->getStart() || $sp2->getEnd() > $sp1->getEnd()) {
                        continue;
                    }

                    // We need to intersect these now
                    $overlaps = $sp1->overlap($sp2);
                    $diff = $sp1->diffSingle($sp2);

                    $overlapPeriod = $overlaps[0];

                    $sp1Start = $diff[0];
                    $sp1End = $diff[1];

                    // Construct new periods
                    $newPeriod1Start = $period1;
                    $newPeriod1Start['conditions'][0]['startDate'] = $sp1Start->getStart()->format('Y-m-d');
                    $newPeriod1Start['conditions'][0]['endDate'] = $sp1Start->getEnd()->format('Y-m-d');

                    $newPeriod2Intersect = $period2;
                    $newPeriod2Intersect['conditions'][0]['startDate'] = $overlapPeriod->getStart()->format('Y-m-d');
                    $newPeriod2Intersect['conditions'][0]['endDate'] = $overlapPeriod->getEnd()->format('Y-m-d');

                    $newPeriod1End = $period1;
                    $newPeriod1End['conditions'][0]['startDate'] = $sp1End->getStart()->format('Y-m-d');
                    $newPeriod1End['conditions'][0]['endDate'] = $sp1End->getEnd()->format('Y-m-d');


                    $newPeriodArray[] = $newPeriod1Start;
                    $newPeriodArray[] = $newPeriod2Intersect;
                    $newPeriodArray[] = $newPeriod1End;

                    /*foreach($overlaps as $period) {
                        dump($period->getStart()->format('Y-m-d') . ' -> ' . $period->getEnd()->format('Y-m-d'));
                    }

                    dump('----');

                    foreach($diff as $period) {
                        dump($period->getStart()->format('Y-m-d') . ' to ' . $period->getEnd()->format('Y-m-d'));
                    }

                    dump('****');*/

                    // We need to break this loop now as we have modified stuff
                    $hasModified = true;
                    break;

                }

            }

            //dump('////////////');

        }

        if ($hasModified) {
            //$newPeriodArray = self::expandPeriods($newPeriodArray);
        }

        return $newPeriodArray;
    }

    /**
     *
     * @param \DateTime $date
     * @param $dayOfWeek - e.g Monday, Tuesday ...
     *
     * @return DateTime
     */
    public static function findNearestDayOfWeek(\DateTime $date, $dayOfWeek): DateTime
    {
        $dayOfWeek = ucfirst($dayOfWeek);
        $daysOfWeek = array(
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday',
        );

        if (!\in_array($dayOfWeek, $daysOfWeek, true)) {
            throw new \InvalidArgumentException('Invalid day of week:' . $dayOfWeek);
        }

        if ($date->format('l') === $dayOfWeek) {
            return $date;
        }

        $previous = clone $date;
        $previous->modify('last ' . $dayOfWeek);

        $next = clone $date;
        $next->modify('next ' . $dayOfWeek);

        $previousDiff = $date->diff($previous);
        $nextDiff = $date->diff($next);

        $previousDiffDays = $previousDiff->format('%a');
        $nextDiffDays = $nextDiff->format('%a');

        if ($previousDiffDays < $nextDiffDays) {
            return $previous;
        }

        return $next;
    }

    public static function convertToObject($date, $default = null)
    {
        if ($date === null || \is_bool($date) || empty($date)) {
            return $default;
        }

        try {

            if (is_numeric($date)) {
                $date = date("Y-m-d H:i:s");
            }

            if (self::isValidDate($date)) {
                return new \DateTime($date, new \DateTimeZone('UTC'));
            }

            return $default;
        } catch (\Exception $ex) {
            return $default;
        }
    }

    public static function isValidDate($dateString)
    {
        return (bool) strtotime($dateString);
    }

    /**
     * @param      $unix
     *
     * @param null $default
     *
     * @return \DateTime
     */
    public static function fromUnixToDateTime($unix, $default = null)
    {
        if (empty($unix) || $unix === null) {
            return $default;
        }

        return new \DateTime(date('Y-m-d H:i:s', $unix), new \DateTimeZone('UTC'));
    }

    /**
     * @param string $date Eg. 2015-06-17
     * @param int $startDay
     * @param bool $returnObjects
     *
     * @return string|\DateTime[]
     */
    public static function getWeekRange($date, $startDay = 1, $returnObjects = false)
    {
        $days = [
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            7 => 'sunday'
        ];

        $date = new \DateTime($date);

        $dtMin = clone($date);

        if ($date->format("N") != $startDay) {
            $dtMin = $date->modify('last ' . $days[$startDay]);
        }

        $dtMax = clone($dtMin);

        if ($returnObjects) {
            return [
                $dtMin,
                $dtMax->modify('+6 days')
            ];
        }

        return [
            $dtMin->format('Y-m-d'),
            $dtMax->modify('+6 days')->format('Y-m-d')
        ];
    }

    public static function getMonthRange($date)
    {
        $date = new \DateTime($date);

        return [
            date("Y-m-01", $date->getTimestamp()),
            date("Y-m-t", $date->getTimestamp())
        ];
    }

    public static function getDateRangeInclusive($startDate, $endDate)
    {
        if ($startDate instanceof \DateTimeInterface || $startDate instanceof \DateTimeInterface) {
            $startDate = $startDate->format("Y-m-d");
        }

        if ($endDate instanceof \DateTimeInterface || $endDate instanceof \DateTimeInterface) {
            $endDate = $endDate->format("Y-m-d");
        }

        $startStamp = strtotime($startDate);
        $endStamp = strtotime($endDate);

        if ($endStamp > $startStamp) {

            $dateArr = [];

            while ($endStamp >= $startStamp) {
                $dateArr[] = date('Y-m-d', $startStamp);
                $startStamp = strtotime(' +1 day ', $startStamp);
            }

            return $dateArr;
        } else {
            return [$startDate];
        }
    }

    public static function ago($unix)
    {
        $time = time() - $unix; // to get the time since that moment

        if ($time < 0) {
            return 'any second now';
        }

        $tokens = array(
            31536000 => 'year',
            2592000  => 'month',
            604800   => 'week',
            86400    => 'day',
            3600     => 'hour',
            60       => 'minute',
            1        => 'second'
        );

        foreach ($tokens as $unit => $text) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);

            return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '');
        }

        return 'Unknown';
    }

    public static function in($unix)
    {
        $time = $unix - time(); // to get the time since that moment

        if ($time < 0) {
            return 'any second now';
        }

        $tokens = array(
            31536000 => 'year',
            2592000  => 'month',
            604800   => 'week',
            86400    => 'day',
            3600     => 'hour',
            60       => 'minute',
            1        => 'second'
        );

        foreach ($tokens as $unit => $text) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);

            return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '');
        }

        return 'unknown';
    }

    public static function formatDateTimeToAgo(\DateTime $dateTime, $daysTillTimestamp = 10)
    {
        $formatter = new DateTimeAgo();

        $formatter->setMaxDaysCount($daysTillTimestamp);

        return $formatter->get($dateTime);
    }

    public static function formatDate($date, $pretty = false)
    {
        if (!$date instanceof \DateTimeInterface) {
            return 'n/a';
        }

        $timezone = 'UTC';
        $timezone = new \DateTimeZone($timezone);
        $date->setTimezone($timezone);

        if ($pretty) {
            return $date->format("Y-m-d - D jS M Y");
        }

        return $date->format("Y-m-d");
    }

    public static function formatDateTimeToIso8601(\DateTime $dateTime, $toUtc = true)
    {
        if ($toUtc) {
            $dateTime = new \DateTime(date("Y-m-d H:i:s", $dateTime->getTimestamp()));
        }

        return $dateTime->format("Y-m-d\TH:i:s%sP");
    }

    public static function formatDateTime(\DateTime $dateTime = null)
    {
        if (is_null($dateTime)) {
            return 'n/a';
        }

        $dateTime->setTimezone(new \DateTimeZone('UTC'));

        return $dateTime->format("Y-m-d H:i:s T");
    }

    public static function formatDateTimeHuman(\DateTime $dateTime)
    {
        $dta = new DateTimeAgo();

        $timestamp = 0;
        if ($dateTime instanceof \DateTimeInterface) {
            $timestamp = $dateTime->getTimestamp();
        } else if (is_numeric($dateTime)) {
            $timestamp = $dateTime;
        }

        $stringDate = date("Y-m-d H:i:s", $timestamp);

        return $dta->get(new \DateTime($stringDate));
    }

    public static function formatUnixToUtc($unix, $dateOnly = false, $default = 'Unknown')
    {
        if (empty($unix)) {
            return $default;
        }

        $extra = ' H:i:s T';
        if ($dateOnly) {
            $extra = '';
        }

        return self::formatDateTime(new \DateTime(date("Y-m-d" . $extra, $unix), new \DateTimeZone('UTC')));
    }

}