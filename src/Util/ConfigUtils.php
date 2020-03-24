<?php

namespace Aptenex\Upp\Util;

use Aptenex\Upp\Parser\ModifiersParser;
use Aptenex\Upp\Parser\Structure\Modifier;
use Aptenex\Upp\Parser\Structure\StructureOptions;

class ConfigUtils
{
    public const NO_CHANNEL_SPECIFIED = 'none';
    /**
     * @param array $config
     * @param null|string $channel
     *
     * @return array
     */
    public static function filterDistributionConditions(array $config, ?string $channel): array
    {
        $newConfig = $config;

        if (!isset($newConfig['data']) || !\is_array($newConfig['data'])) {
            return $config;
        }

        // The default functionality of the parse is to include the modifiers that have a dist. channel condition
        // but no channel is set. This util function will STRIP out any modifier that has a dist. channel condition
        // even if the $channel = '' OR $channel = null. To achieve this "strip" with the same codebase, we will just
        // set the channel to NO_CHANNEL_SPECIFIED which achieves the same goal

        $so = new StructureOptions();
        $so->setDistributionChannel(!empty($channel) ? $channel : self::NO_CHANNEL_SPECIFIED);

        foreach($config['data'] as $indexCc => $cc) {
            if (!isset($cc['modifiers'])) {
                continue;
            }
            $newModifiers = array_map(
                static function ($mo) {
                /** @var Modifier $mo */
                return $mo->__toArray();
            }, (new ModifiersParser())->parse($cc['modifiers'], $so));
            $newConfig['data'][$indexCc]['modifiers'] = $newModifiers;
        }

        return $newConfig;
    }

    /**
     * @param array $periods
     *
     * @return array
     */
    public static function expandPeriods(array $periods): array
    {
        $originalPeriods = $periods;

        // Loop through each period and determine whether any other period intersects with it
        // if it does intersect COMPLETELY then we need to split it into 3 periods. If it half intersects
        // then split it into two

        // The method for expanding these periods is:
        // 1. Sort from longest to shortest into an array & take the total length of the period set
        // 2. Create an array of dates from start to end
        // 3. In the sorted period array expand each period into date => period spanning the whole length so some dates => null
        // 4. This is a 2-dimensional array, with the lowest index being the longest period which contains the expanded dates
        // 5. Loop through the array of dates, perform a sequential lookup (from high to low) through the indexes of the 2-dimensional period array
        // 6. Pick the first period that matches and assign it to that date - which will be the smallest period for that date which is most likely nested
        // The reason we do high to low, is that low to high we will always have to loop to the end to check if any period exists for that date
        // whereas going from high to low, means that we can stop immediately after one has been found as it was sorted in the reverse order prior

        $hasAnyNestedDates = false;

        // Set these as date times so we dont need to do a null check - only need comparisons
        $earliestDate = new \DateTime('2100-01-01');
        $latestDate = new \DateTime('2000-01-01');

        foreach($periods as $index => $period) {
            $periods[$index]['_epId'] = $index; // Unique identifier
            list ($sd, $ed) = DateUtils::getStartEnd($period);

            if ($sd < $earliestDate) {
                $earliestDate = $sd;
            }

            if ($ed > $latestDate) {
                $latestDate = $ed;
            }

            // Lets also check if there are no nested periods - if not then we can just return the original periods as they are
            foreach($periods as $index2 => $period2) {
                list ($sd2, $ed2) = DateUtils::getStartEnd($period2);

                if ($sd == $sd2 && $ed == $ed2) {
                    continue; // Skip as comparing the same dates with itself will always intersect
                }

                $sp1 = \Spatie\Period\Period::make($sd, $ed);
                $sp2 = \Spatie\Period\Period::make($sd2, $ed2);

                if ($sp1->overlapsWith($sp2)) {
                    $hasAnyNestedDates = true;
                    break; // Break this loop as we only need 1 nested to NOT skip the whole expansion
                }
            }
        }

        if ($hasAnyNestedDates === false) {
            return $originalPeriods;
        }

        usort($periods, function($a, $b) {
            list($asd, $aed) = DateUtils::getStartEnd($a);
            $aNights = $asd->diff($aed)->days;

            list($bsd, $bed) = DateUtils::getStartEnd($b);
            $bNights = $bsd->diff($bed)->days;

            // Most nights at start of array
            return $aNights < $bNights;
        });

        // We now need to go through each period and convert them all into maps of the dates they contain in a 2 dimensional array
        // format is [ ['date' => period] ]
        $sortedExpandedPeriodMap = [];
        foreach($periods as $period) {
            list($sd, $end) = DateUtils::getStartEnd($period);

            $singlePeriodExpanded = [];

            DateUtils::getDateRangeInclusive($sd, $end, function ($date) use (&$singlePeriodExpanded, $period) {
                $singlePeriodExpanded[$date] = $period;
            });

            $sortedExpandedPeriodMap[] = $singlePeriodExpanded;
        }

        // Now we look through expanded date array and then through the map from BACK to front and pull the earliest period that
        // has a mapping and use that. We take the dates at which this occurred and then use that to create the new periods

        $newPeriodArray = [];
        $nestedCount = \count($sortedExpandedPeriodMap);

        DateUtils::getDateRangeInclusive($earliestDate, $latestDate, function ($date) use (&$newPeriodArray, $nestedCount, $sortedExpandedPeriodMap) {
            for ($i = $nestedCount; $i >= 0; $i--) {
                if (isset($sortedExpandedPeriodMap[$i][$date])) {
                    $newPeriodArray[$date] = $sortedExpandedPeriodMap[$i][$date];
                    break; // Break this loop as we've found it
                }

                $newPeriodArray[$date] = null;
            }
        });

        // Now we need to normalize the periods, we need to find a way to identify each period (change in dates most likely)
        $normalizedPeriodArray = [];

        $mergingData = null;
        $previousPeriod = null;
        foreach($newPeriodArray as $date => $period) {

            if ($period !== null && $mergingData === null) {
                // Initialize new one
                $mergingData = ['startDate' => $date, 'endDate' => $date, 'period' => $period];
            } else if ($period === null && $mergingData === null) {
                // Skip
            } else if ($period === null && $mergingData !== null) {
                // Period is null we need to CLOSE the merging period

                $newPeriod = $mergingData['period'];

                unset($newPeriod['_epId']);
                $newPeriod['conditions'][0]['startDate'] = $mergingData['startDate'];
                $newPeriod['conditions'][0]['endDate'] = $mergingData['endDate'];

                $normalizedPeriodArray[] = $newPeriod;
                $mergingData = null;
            } else if ($period !== null && $mergingData !== null) {
                // Period exists see if we need to continue or not
                if ($period['_epId'] === $mergingData['period']['_epId']) {
                    // Same period so we need to update
                    $mergingData['endDate'] = $date;
                } else {
                    // Different periods we need to re-initialize
                    $newPeriod = $mergingData['period'];

                    unset($newPeriod['_epId']);
                    $newPeriod['conditions'][0]['startDate'] = $mergingData['startDate'];
                    $newPeriod['conditions'][0]['endDate'] = $mergingData['endDate'];

                    $normalizedPeriodArray[] = $newPeriod;
                    // Re-initialize
                    $mergingData = ['startDate' => $date, 'endDate' => $date, 'period' => $period];
                }
            }


            $previousPeriod = $period;
        }

        // If merging data is still there we need to close
        if ($mergingData !== null) {
            $newPeriod = $mergingData['period'];

            unset($newPeriod['_epId']);
            $newPeriod['conditions'][0]['startDate'] = $mergingData['startDate'];
            $newPeriod['conditions'][0]['endDate'] = $mergingData['endDate'];
            $normalizedPeriodArray[] = $newPeriod;
            $mergingData = null;
        }

        return $normalizedPeriodArray;
    }

}