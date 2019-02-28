<?php

namespace Los\Transformer;

use Los\LosRecords;

/**
 * This array -> record transformer mimics Airbnb's format near identical just with multiple currencies
 *
 * @package Los\Transformer
 */
class ArrayRecordTransformer implements RecordTransformerInterface
{

    /**
     * @param LosRecords $records
     * @return array|mixed
     */
    public function transform(LosRecords $records)
    {
        $data = [];

        foreach($records->getRecords() as $currency => $recordSet) {
            $data[$currency] = $this->transformCurrencySet($recordSet);
        }

        return $data;
    }

    /**
     * @param array $recordSet
     * @return array
     */
    public function transformCurrencySet(array $recordSet): array
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
                        $currencySet[] = vsprintf('%s,%s,%s', [
                            $previousSingleRecord['date'],
                            $maxGuestCountForSameHash,
                            implode(',', $previousSingleRecord['rates'])
                        ]);

                        $maxGuestCountForSameHash = 0;
                    }

                    $currencySet[] = vsprintf('%s,%s,%s', [
                        $singleRecord['date'],
                        $singleRecord['guest'],
                        implode(',', $singleRecord['rates'])
                    ]);
                }

                $previousSingleRecord = $singleRecord;
            }
        }

        return $currencySet;
    }

}