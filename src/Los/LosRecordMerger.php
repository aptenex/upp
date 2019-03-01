<?php

namespace Los;
use Aptenex\Upp\Exception\CannotGenerateLosException;

/**
 * Class LosRecordMerger
 *
 * This will take any amount of LosRecords and merge them by their dates
 * It will also take the longest metric time as the total time taken to generate the records & total up the others
 *
 * @package Los
 */
class LosRecordMerger
{

    /**
     * @param LosRecords[] $losRecords
     *
     * @return LosRecords
     *
     * @throws CannotGenerateLosException
     */
    public function merge(array $losRecords): LosRecords
    {
        $merged = [];
        $foundCurrency = null;

        $totalRan = 0;
        $totalMax = 0;
        $longestTime = 0;

        foreach($losRecords as $lrItem) {
            $totalRan += $lrItem->getMetrics()->getTimesRan();
            $totalMax += $lrItem->getMetrics()->getMaxPotentialRuns();

            if ($lrItem->getMetrics()->getTotalTime() > $longestTime) {
                $longestTime = $lrItem->getMetrics()->getTotalTime();
            }

            if ($foundCurrency === null) {
                $foundCurrency = $lrItem->getCurrency();
            } else if ($foundCurrency !== $lrItem->getCurrency()) {
                throw new CannotGenerateLosException('Attempting to merge LosRecords with different currencies');
            }

            foreach($lrItem->getRecords() as $date => $priceSet) {
                $merged[$date] = $priceSet;
            }
        }

        if ($foundCurrency === null) {
            throw new CannotGenerateLosException('Could not merge LosRecords');
        }

        $mergedRecords = LosRecords::makeFromExisting($foundCurrency, $merged);
        $mergedRecords->getMetrics()->setTimesRan($totalRan);
        $mergedRecords->getMetrics()->setMaxPotentialRuns($totalMax);
        $mergedRecords->getMetrics()->setTotalTime($longestTime);

        return $mergedRecords;
    }

}