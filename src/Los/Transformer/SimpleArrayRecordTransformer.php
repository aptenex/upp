<?php

namespace Los\Transformer;

use Los\LosRecords;

/**
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
            foreach($dateSet as $index => $singleRecord) {
                $currencySet[] = vsprintf('%s,%s,%s', [
                    $singleRecord['date'],
                    $singleRecord['guest'],
                    implode(',', $singleRecord['rates'])
                ]);
            }
        }

        return $currencySet;
    }

}