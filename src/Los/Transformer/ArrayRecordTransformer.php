<?php

namespace Los\Transformer;

use Aptenex\Upp\Exception\CannotGenerateLosException;
use Los\LosRecords;

/**
 * This array -> record transformer mimics Airbnb's format near identical just with multiple currencies
 *
 * @package Los\Transformer
 */
class ArrayRecordTransformer extends BaseRecordTransformer
{

    /**
     * This will select a specific currency
     *
     * @param LosRecords $records
     * @param TransformOptions $options
     *
     * @return array Format is [ los_string, los_string, los_string ]
     *
     * @throws CannotGenerateLosException
     */
    public function transform(LosRecords $records, TransformOptions $options): array
    {
        if (empty($records->getRecords())) {
            return [];
        }

        $currencySet = $records->getRecords();

        $cData = null;
        foreach($currencySet as $setCurrency => $dateSet) {
            if ($options->getCurrency() === null) {
                $cData = $dateSet;
                break;
            }

            if ($setCurrency === $options->getCurrency()) {
                $cData = $dateSet;
                break;
            }
        }

        if ($cData === null) {
            throw new CannotGenerateLosException('Could not locate valid currency for LosRecords');
        }

        return $this->transformCurrencySet($cData, $options);
    }

    /**
     * @param array $recordSet
     * @param TransformOptions $options
     *
     * @return array
     */
    public function transformCurrencySet(array $recordSet, TransformOptions $options): array
    {
        $currencySet = [];

        $computedEmptyHash = null;
        foreach($recordSet as $date => $dateSet) {
            // We need a lookahead to merge the first guest count if a few guest counts have the same rate
            // this is because there is no way to compare the first hash to the previous hash on the first one
            $firstLookahead = $dateSet[1] ?? null;
            $previousSingleRecord = null;
            $maxGuestCountForSameHash = 0;
            $guestEntries = count($dateSet);
            foreach($dateSet as $index => $singleRecord) {

                if ($index === 0 && $computedEmptyHash === null) {
                    // We need to compute the hash of all 0's in a string, but we do it here as this transformer
                    // does not know the MAXIMUM stay length so we can just get it here on the very first instance
                    $computedEmptyHash = sha1(implode(',', array_fill(0, count($singleRecord['rates']), 0)));
                }

                if (
                    (
                        ($previousSingleRecord !== null && $previousSingleRecord['rateHash'] === $singleRecord['rateHash']) ||
                        ($index === 0 && $firstLookahead !== null && $firstLookahead['rateHash'] === $singleRecord['rateHash'])
                    ) &&
                    $index !== ($guestEntries - 1) // If we reach the END of the guest range and the hash still has not changed - we need to add at least one entry!
                ) {
                    // Skip
                    $maxGuestCountForSameHash = $singleRecord['guest'];
                } else {

                    if ($singleRecord['rateHash'] === $computedEmptyHash) {
                        continue;
                    }

                    // Hash does not match that means we need to finally add the entry for the previous entries
                    // We also need to perform the check if this is the LAST index because
                    // if all the rates are exactly the same then $maxGuestCountForSameHash !== 0 = true
                    // so we'll be adding two records one for the previous guest count and one for the last index guest count
                    // this extra index check
                    if ($maxGuestCountForSameHash !== 0 && $index !== ($guestEntries - 1)) {
                        $currencySet[] = $this->generateLosRecordString($previousSingleRecord, $options);

                        $maxGuestCountForSameHash = 0;
                    }

                    $currencySet[] = $this->generateLosRecordString($singleRecord, $options);
                }

                $previousSingleRecord = $singleRecord;
            }
        }

        return $currencySet;
    }

}