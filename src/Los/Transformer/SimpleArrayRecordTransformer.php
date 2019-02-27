<?php

namespace Los\Transformer;

use Los\LosRecords;

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

            $data[$currency] = [];

            foreach($recordSet as $date => $dateSet) {
                foreach($dateSet as $singleRecord) {
                    $implodedRates = implode(',', $singleRecord['rates']);
                    for ($i = $singleRecord['minGuest']; $i <= $singleRecord['maxGuest']; $i++) {
                        $data[$currency][] = vsprintf('%s,%s,%s', [
                            $singleRecord['date'],
                            $i,
                            $implodedRates
                        ]);
                    }
                }
            }
        }

        return $data;
    }

}