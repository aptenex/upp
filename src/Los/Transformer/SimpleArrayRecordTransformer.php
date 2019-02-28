<?php

namespace Los\Transformer;

use Los\LosRecords;

/**
 * This array -> record transformer mimics Airbnb's format near identical just with multiple currencies
 *
 * @package Los\Transformer
 */
class SimpleArrayRecordTransformer implements RecordTransformerInterface
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

        foreach($recordSet as $date => $dateSet) {
            // We need a lookahead to merge the first guest count if a few guest counts have the same rate
            // this is because there is no way to compare the first hash to the previous hash on the first one
            $firstLookahead = $dateSet[1] ?? null;
            $previousSingleRecord = null;
            $maxGuestCountForSameHash = 0;
            foreach($dateSet as $index => $singleRecord) {
                if (
                    ($previousSingleRecord !== null && $previousSingleRecord['rateHash'] === $singleRecord['rateHash']) ||
                    ($index === 0 && $firstLookahead !== null && $firstLookahead['rateHash'] === $singleRecord['rateHash'])
                ) {
                    // Skip
                    $maxGuestCountForSameHash = $singleRecord['guest'];
                } else {

                    // Hash does not match that means we need to finally add the entry for the previous entries
                    if ($maxGuestCountForSameHash !== 0) {
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