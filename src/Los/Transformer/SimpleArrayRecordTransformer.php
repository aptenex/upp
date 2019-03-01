<?php

namespace Los\Transformer;

use Los\LosRecords;

/**
 * @package Los\Transformer
 */
class SimpleArrayRecordTransformer extends BaseRecordTransformer
{

    /**
     * @param LosRecords $records
     * @param TransformOptions $options
     *
     * @return array|mixed
     */
    public function transform(LosRecords $records, TransformOptions $options)
    {
        $data = [];

        foreach($records->getRecords() as $currency => $recordSet) {
            $data[$currency] = $this->transformCurrencySet($recordSet, $options);
        }

        return $data;
    }

    /**
     * @param array $recordSet
     * @param TransformOptions $options
     * @return array
     */
    public function transformCurrencySet(array $recordSet, TransformOptions $options): array
    {
        $currencySet = [];

        foreach($recordSet as $date => $dateSet) {
            foreach($dateSet as $index => $singleRecord) {
                $currencySet[] = $this->generateLosRecordString($singleRecord, $options);
            }
        }

        return $currencySet;
    }

}