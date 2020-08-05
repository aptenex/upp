<?php

namespace Aptenex\Upp\Util;

/**
 * Class AvailabilityUtils
 *
 * This is pulled from Lycan as a direct clone, this should be used instead of Lycan's version throughout.
 * Please replace instances as you come across them
 *
 * @package Aptenex\Upp\Util
 */
class AvailabilityUtils
{

    /**
     * This function assumes all strings start on the same date. It merges all given availability strings via
     * applying all blocks into a single string
     *
     * @param array $strings
     * @return string
     */
    public static function mergeAvailabilityStrings(array $strings): string
    {
        $availability = [];

        foreach($strings as $string) {
            if (empty($string) || $string === null) {
                continue;
            }

            $chars = str_split($string);

            foreach($chars as $index => $char) {
                $current = $availability[$index] ?? null;

                if ($current === null) {
                    $availability[$index] = $char;
                } else {
                    // We want to essentially not allow any Y updates on existing chars that are N
                    if ($current === 'Y' && $char === 'N') {
                        $availability[$index] = $char; // Blocking the available
                    }
                }
            }
        }

        return implode('', $availability);
    }

    /**
     * This will accept a start date and an array of items that follow the format ['start' => \DateTime, 'end' => \DateTime]
     * into a availability sequence. This function expects no date ranges to be overlapping / nested.
     *
     * It will convert these date ranges into the designated char, eg if Y then non-date ranges will be N and vice versa
     *
     * @param \DateTime $startDate
     * @param array $ranges
     * @param int $stringLength The amount of days to generate the availability for
     * @param string $dateExistsChar
     *
     * @return string
     */
    public static function convertDateRangesToAvailabilityString(\DateTime $startDate, array $ranges, int $stringLength = 1095, string $dateExistsChar = 'Y'): string
    {
        try {
            $endDate =(clone $startDate)->add(new \DateInterval(sprintf('P%sD', $stringLength)));
        } catch (\Exception $e) {
            return null;
        }

        $availability = [];

        $doesNotExistChar = $dateExistsChar === 'Y' ? 'N' : 'Y';

        // We need to expand the ranges
        $expandedRanges = [];
        foreach($ranges as $item) {
            $expandedItem = DateUtils::getDateRangeInclusive($item['start'], $item['end']);

            foreach($expandedItem as $date) {
                $expandedRanges[$date] = $date;
            }
        }

        foreach(DateUtils::getDateRangeInclusive($startDate, $endDate) as $date) {
            if (isset($expandedRanges[$date])) {
                $avResult = $dateExistsChar;
            } else {
                $avResult = $doesNotExistChar;
            }

            $availability[] = $avResult;
        }

        return implode('', $availability);
    }

    /**
     * @param array $ranges
     * @param int $addDaysToDuration
     * @param bool $dateAreBookings
     *
     * @return array
     *               The purpose of this method is to pass a collection bookings and normalise them
     *               so that you can received a single string from a colleciton of ranges.
     *               This will return the startDatum and the string...
     *               EXAMPLE RESPONSE
     *               'startDatum' => "2019-01-01"
     *               'sequence' => "YYYYYYYYYYYYYYYYYYYYNNNNNNNNNNNNNNNNNYYYYYYYYYYYYY"
     */
    public static function getAvailabilityRange($ranges, $addDaysToDuration = 0, $dateAreBookings = true)
    {
        $minDate = null;
        $maxDate = null;

        foreach ($ranges as $bookings) {
            $minDate = ($bookings['startDate'] < $minDate || is_null($minDate)) ? $bookings['startDate'] : $minDate;
            $maxDate = ($bookings['endDate'] > $maxDate || is_null($maxDate)) ? $bookings['endDate'] : $maxDate;
        }

        if (!$minDate instanceof \DateTime) {
            $s = new \DateTime($minDate);
            $s->setTime(0, 0, 0);
        } else {
            $s = $minDate->setTime(0, 0, 0);
        }
        // max date
        $md = $maxDate instanceof \DateTime ? $maxDate : new \DateTime($maxDate);
        // THIS IS THE PROBLEM!!!! FINALLY. You need to know how many MIDNIGHTS have passed. That is the difference.
        // Currently, you are just checking the number of 24 hours.
        $md->setTime(0, 0);
        $difference = (int) $s->diff($md)->format('%a');

        // UPDATE - Added so that we can get a single availability slice...
        if (0 === $difference) {
            $difference = 1;
        }

        if ($difference > 0 && $difference < 3000) {
            $stretch = array_fill(0, $difference, $dateAreBookings ? 'Y' : 'N');
        } else {
            return [
                'startDatum' => $s,
                'sequence'   => null,
            ];
        }

        // Second Pass
        foreach ($ranges as $event) {
            $sd = is_a($event['startDate'], 'DateTime') ? $event['startDate'] : new \DateTime($event['startDate']);
            $ed = is_a($event['endDate'], 'DateTime') ? $event['endDate'] : new \DateTime($event['endDate']);

            // FINAL FIX (3) -> EVERYTRHING BELOW HERE WAS WRONG.
            // YOU NEED TO SET THE DATE TIMES TO 0 0 ....
            // http://stackoverflow.com/questions/5215190/calculating-how-many-midnights-is-one-date-past-another-in-php
            $sd->setTime(0, 0);
            $ed->setTime(0, 0);
            // NEWFIX (WRONG AGAIN): Adding one day was wrong. I've removed it....
            // WRONG-> We have to add one day, because of our the days, are only full days
            // WRONG-> And we are actually staying 2 nights, (becayuse of the hours)
            // WRONG-> FIX: ALWAYS ADD 1 DAY AT LEAST ...
            $duration = (int) $sd->diff($ed)->format('%a') + $addDaysToDuration;
            $offset = $s->diff($sd)->format('%a');
            // Don't know why duration would not be positive.. but was getting errors.... so meh.
            if ($duration <= 0) {
                $bs = '';
            } else {
                if (isset($event['bookingType']) && -1 === $event['bookingType']) {
                    $bs = array_fill(0, $duration, 'Q');
                } else {
                    $bs = array_fill(0, $duration, 1);
                }
            }

            array_splice($stretch, $offset, $duration, $bs);
        }

        // Now based on what mode we are in, replace all 1's with N and 0 with Y respectively. (Based on if we are bookings or availabilities)
        $stringStretch =
            str_replace('1', $dateAreBookings ? 'N' : 'Y',
                str_replace('0', $dateAreBookings ? 'Y' : 'N',
                    implode($stretch)
                ));

        return [
            'startDatum' => $s,
            'sequence'   => $stringStretch,
        ];
    }

    /**
     * Takes an array with parameters startDatum: \DateTime|YYYY-MM-DD, sequence: String
     * and converts it into an array of sequences - array['YYYY','NNN','QQ']
     *
     * @param $set
     * @return array
     * @throws \Exception
     */
    public static function extractRanges($set): array
    {
        // On an empty set.. it.
        if (empty($set['sequence'])) {
            $set['sequence'] = 'Y';
        }

        // Convert into \DateTime object
        if (!($set['startDatum'] instanceof \DateTime)) {
            if (isset($set['startDatum']['date'])) {
                $set['startDatum'] = new \DateTime($set['startDatum']['date']);
            } else {
                $set['startDatum'] = new \DateTime($set['startDatum']);
            }
        }

        $aRanges = [];
        $startDatum = $set['startDatum'];
        $availability = $set['sequence'];

        $availability = str_split(strtoupper($availability));

        $lastChar = null;

        foreach ($availability as $index => $availabilityChar) {

            if ($availabilityChar === $lastChar) {
                $aRanges[count($aRanges) - 1] .= $availabilityChar;
            } else {
                $aRanges[] = $availabilityChar;
            }

            $lastChar = $availabilityChar;
        }

        return [$startDatum, $aRanges];
    }

    /**
     * @param array $set
     *
     * @return array
     *               This is used to reduce multiple arrays... "YYYNNNNN", "YYYNNNNNYYY" into a single string,
     *
     * @throws \Exception
     */
    public static function getAvailabilityFromDatumAndString($set)
    {
        if (empty($set) || (!isset($set['startDatum'], $set['sequence']))) {
            return [];
        }
        list($startDatum, $aRanges) = self::extractRanges($set);

        $availability = [];
        $runningOffset = 0;
        foreach ($aRanges as $range) {
            $duration = strlen($range);
            $s = clone $startDatum;
            $e = clone $startDatum;
            if ('Q' === strtoupper($range[0]) || 'N' === $range[0]) {
                $p = [
                    'startDate' => $s->add(new \DateInterval('P' . $runningOffset . 'DT14H')),
                    'endDate'   => $e->add(new \DateInterval('P' . ($runningOffset + $duration) . 'DT10H')),
                ];

                $runningOffset = $duration + $runningOffset;
            } else {
                $runningOffset = $duration + $runningOffset;
                continue;
            }

            if ('Q' === strtoupper($range[0])) {
                $p['conditional'] = true;
            }
            $availability[] = $p;
        }

        return $availability;
    }

    /**
     * This method return date ranges with type without intersection.
     *
     * Expected format for $set = ['startDatum' => 'YYYY-MM-DD', 'sequence' => 'YYNNNNNNYYY...']
     *
     * @param $set
     * @param bool $dateStrings Return an array of date strings instead
     *
     * @return array
     *
     * @throws \Exception
     */
    public static function getAvailabilityRangesIncludingState($set, $dateStrings = false): array
    {
        if (empty($set) || !isset($set['startDatum'], $set['sequence'])) {
            return [];
        }

        /** @var \DateTime $startDatum */
        [$startDatum, $aRanges] = self::extractRanges($set);

        $availability = [];
        $sequencePosition = 0;

        foreach ($aRanges as $rangeIndex => $range) {
            $length = strlen($range);

            $startInterval = $sequencePosition;

            // If it is a single day, we do not add anything onto the interval
            $endInterval = $startInterval + ($length === 1 ? 0 : $length - 1);

            $startDate = (new \DateTime($startDatum->format('Y-m-d')))
                ->add(new \DateInterval(sprintf('P%sD', $startInterval)));

            $endDate = (new \DateTime($startDatum->format('Y-m-d')))
                ->add(new \DateInterval(sprintf('P%sD', $endInterval)));

            // Now we can advance the sequence
            $sequencePosition += $length;

            // Set data
            if ($length === 1) {
                $startDate->setTime(10, 0, 0);
                $endDate->setTime(14, 0, 0);
            } else {
                $startDate->setTime(14, 0, 0);
                $endDate->setTime(10, 0, 0);
            }

            $p = [
                'startDate' => $startDate,
                'endDate'   => $endDate,
                'type'      => $range[0],
            ];

            if ($range[0] === 'Q') {
                $p['conditional'] = true;
            }

            $availability[] = $p;
        }

        if ($dateStrings) {
            $availability = array_map(function ($item) {
                $data = [
                    'startDate' => $item['startDate']->format('Y-m-d H:i:s'),
                    'endDate'   => $item['endDate']->format('Y-m-d H:i:s'),
                    'type' => $item['type']
                ];

                if (isset($item['conditional'])) {
                    $data['conditional'] = $item['conditional'];
                }

                return $data;
            }, $availability);
        }

        return $availability;
    }

    /**
     * @param array @availability
     * @param \DateTime $fromDate
     * @param \DateTime $toDate
     *
     * @return string
     *
     *  Pass the availability which should contain a startDatum and a sequence...
     * And pass $checkRange which contains array("checkInDate"=> $checkIn, "checkOutDate" => $checkOut) as ISO8601 formatted
     * You will then be returned a string representing the availability YNNNNYYYYNNNNNYYYYYYY between the startDate and endDate passed.
     * */
    public static function sliceAvailabilityString(array $availability = null, \DateTime $fromDate, \DateTime $toDate)
    {
        return self::_sliceAvailability($availability, ['fromDate' => $fromDate->format('Y-m-d'), 'toDate' => $toDate->format('Y-m-d')]);
    }

    // Returns a sliced range for YYYNNNNYY between two dates, based on the availability array passed.
    // $availability should container "availability" key with string, and startDatum.
    // fromDate and toDate should be a DateTime object......
    private static function _sliceAvailability(array $stringRange = null, $checkRange): string
    {
        if ($stringRange['startDatum'] instanceof \DateTime) {
            $stringRange['startDatum'] = $stringRange['startDatum']->format('Y-m-d');
        }
        if ($stringRange === null || null === $stringRange['startDatum']) {
            $now = new \DateTime();
            $stringRange = [
                'startDatum' => $now->modify('midnight')->format('Y-m-d'),
                'sequence'   => 'Y',
            ]; // You need at least one day available.
        }
        $padToBeginning = 0;
        if ($checkRange['fromDate'] < $stringRange['startDatum']) {
            // We have to "PAD" the string
            $s = new \DateTime($checkRange['fromDate']);
            $e = new \DateTime($stringRange['startDatum']);
            // Need to add One Day...
            $padToBeginning = ((int) $s->diff($e)->format('%a'));
        }

        // Make sure $stringRange has startDarum
        if (isset($stringRange['startDate'])) {
            $stringRange['startDatum'] = $stringRange['startDate'];
            unset($stringRange['startDate']);
        }

        if (isset($stringRange['startDatum']) && is_string($stringRange['startDatum'])) {
            $stringRange['startDatum'] = new \DateTime($stringRange['startDatum']);
        }

        $s = new \DateTime($checkRange['fromDate']);
        $e = new \DateTime($checkRange['toDate']);
        // Need to add One Day...
        $differenceToSlice = ((int) $s->diff($e)->format('%a')) + 1;
        $checkRange = [
            'startDate' => $s->format('Y-m-d'),
            'endDate'   => $e->format('Y-m-d'),
        ];

        // This just returns a string.. of NNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN representative of the days passed.
        // We can then use this to compare to the $stringRange to extract a comparative/respective range.
        $datumCheckRange = self::getAvailabilityRange([$checkRange], 1); // Add a single day.

        // This calculates the offset that we can then splice upon
        // You also need to make sure that the $startDatum
        if (0 == $padToBeginning) {
            $difference = $datumCheckRange['startDatum']->diff($stringRange['startDatum'])->format('%a');
        } else {
            $difference = 0;
            $stringRange['sequence'] = str_repeat('N', $padToBeginning) . $stringRange['sequence'];
            $stringRange['startDatum'] = $datumCheckRange['startDatum'];
        }
        $startIndex = $difference;
        $availability = str_split($stringRange['sequence']);

        // If the check date starts before the availability date, then we know that all the days leading up to it are available...
        // UPDATE APRIL 2019 - Are they really available? This could be a problem.... switching to NOT available.
        if ($datumCheckRange['startDatum'] < $stringRange['startDatum']) {
            // $difference = $difference * -1;
            $availability = array_merge(array_fill(0, $difference + 1, 'N'), $availability);
            $stringRange['startDatum'] = $datumCheckRange['startDatum'];
            $startIndex = 0;
            $difference = 0;
        }

        // This is the NIGHTS. (Remeber it will be one less.)
        $nights = (int) $s->diff($e)->format('%a');

        // Need to reduce the offset by one... ( I think? )
        $splice = array_splice($availability, $startIndex, strlen($datumCheckRange['sequence']));
        if (count($splice) < ($nights + 1)) {
            $pad = $nights + 1 - count($splice);
            $splice = array_merge($splice, array_fill(count($splice), $pad, 'N')); // D = DEFAULT...
        }

        return implode($splice);
    }

    public static function canocaliseSequences($collection): ?array
    {
        $minDate = null;
        // When canonicalising, we don't want to allow any dates earlier than today through..
        // What's the point.

        if (empty($collection)) {
            return null;
        }

        foreach ($collection as $availabilitySequence) {
            if (empty($availabilitySequence)) {
                continue;
            }
            $startDatumKey = 'startDatum';
            $minDate = ($availabilitySequence[$startDatumKey] < $minDate || $minDate === null) ? $availabilitySequence[$startDatumKey] : $minDate;
        }

        if (!($minDate instanceof \DateTime)) {
            throw new \RuntimeException('minDate must be of type DateTime');
        }

        // Now pad values...
        $availabilityStrings = [];
        foreach ($collection as $availabilitySequence) {
            if (empty($availabilitySequence)) {
                continue;
            }
            $sequenceKey = 'sequence';
            $startDatumKey = 'startDatum';
            $difference = (int) $minDate->diff($availabilitySequence[$startDatumKey])->format('%a');
            if ($difference > 0) {
                $bs = implode(array_fill(0, $difference, 0));
                $availabilityStrings[] = str_replace(['Y', 'N'], [0, 1], $bs . $availabilitySequence[$sequenceKey]);
            } else {
                $availabilityStrings[] = str_replace(['Y', 'N'], [0, 1], $availabilitySequence[$sequenceKey]);
            }
        }

        $res = array_reduce($availabilityStrings, static function ($a, $b) {
            return (string) $a | (string) $b;
        }, 0);

        $now = new \DateTime();
        $diffFromToday = (new \DateTime())->diff($minDate)->format('%a');

        if ($now > $minDate) {
            $minDate = $now;
            $res = substr($res, $diffFromToday);
        }

        return [
            'startDatum' => $minDate->setTime(0, 0, 0),
            'sequence'   => str_replace([0, 1], ['Y', 'N'], (string) $res)
        ];
    }
}
