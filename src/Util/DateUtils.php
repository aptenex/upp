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
     * @param array $period
     * @return \DateTime[]
     */
    public static function getStartEnd(array $period): array
    {
        $dc = $period['conditions'][0];
        return [new \DateTime($dc['startDate']), new \DateTime($dc['endDate'])];
    }
    
    /**
     * @deprecated Please use ConfigUtils::expandPeriods()
     *
     * @param array $periods
     *
     * @return array
     */
    public static function expandPeriods(array $periods): array
    {
        return ConfigUtils::expandPeriods($periods);
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

    /**
     * @param $startDate
     * @param $endDate
     * @param callable|null $callback Only parameter is currentDate. Useful to reduce iterations
     * @return array
     */
    public static function getDateRangeInclusive($startDate, $endDate, callable $callback = null)
    {
        if ($startDate instanceof \DateTimeInterface) {
            $startDate = $startDate->format("Y-m-d");
        }

        if ($endDate instanceof \DateTimeInterface) {
            $endDate = $endDate->format("Y-m-d");
        }

        $startStamp = strtotime($startDate);
        $endStamp = strtotime($endDate);

        if ($endStamp > $startStamp) {

            $dateArr = [];

            while ($endStamp >= $startStamp) {
                $startDate = date('Y-m-d', $startStamp);

                if (\is_callable($callback)) {
                    $callback($startDate);
                }

                $dateArr[] = $startDate;
                $startStamp = strtotime(' +1 day ', $startStamp);
            }

            return $dateArr;
        } else {
            if (\is_callable($callback)) {
                $callback($startDate);
            }

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

    /**
     * @param DateTime $start
     * @param DateTime $end
     * @param int $chunks
     *
     * @return array
     */
    public static function getDateChunks(\DateTime $start, \DateTime $end, int $chunks): array
    {
        $dateRange = self::getDateRangeInclusive($start, $end);

        // Since array_chunk will return arrays of max length $chunk, we need to change this $chunk
        // into the actual amount of days per chunk

        $actualChunkCount = ceil(count($dateRange) / $chunks);

        $chunkedList = array_chunk($dateRange, $actualChunkCount);

        $chunkedDates = [];

        foreach($chunkedList as $chunkItem) {
            $chunkedDates[] = [
                new \DateTime(array_shift($chunkItem)),
                new \DateTime(array_pop($chunkItem)),
            ];
        }

        return $chunkedDates;
    }

}