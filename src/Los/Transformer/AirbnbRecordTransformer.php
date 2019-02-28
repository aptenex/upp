<?php

namespace Los\Transformer;

use Aptenex\Upp\Exception\CannotGenerateLosException;
use Los\LosRecords;

/**
 * @package Los\Transformer
 */
class AirbnbRecordTransformer implements RecordTransformerInterface
{

    /**
     * @param LosRecords $records
     *
     * @param string|null $currency If null it will pull the first currency
     * @return array Format is [ los_string, los_string, los_string ]
     *
     * @throws CannotGenerateLosException
     */
    public function transform(LosRecords $records, string $currency = null): array
    {
        if (empty($records->getRecords())) {
            return [];
        }

        $currencySet = $records->getRecords();

        $cData = null;
        foreach($currencySet as $setCurrency => $dateSet) {
            if ($currency === null) {
                $cData = $dateSet;
                break;
            }

            if ($setCurrency === $currency) {
                $cData = $dateSet;
                break;
            }
        }

        if ($cData === null) {
            throw new CannotGenerateLosException('Could not locate valid currency for LosRecords');
        }

        return (new SimpleArrayRecordTransformer())->transformCurrencySet($cData);
    }

}