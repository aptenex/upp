<?php

namespace Aptenex\Upp\Los\Transformer;

use Aptenex\Upp\Los\LosRecords;

abstract class BaseRecordTransformer implements RecordTransformerInterface
{

    abstract public function transform(LosRecords $records, TransformOptions $options);

    public function generateLosRecordString($record, TransformOptions $options): string
    {
        return vsprintf('%s,%s,%s', [
            $record['date'],
            $record['guest'],
            implode(',',  $this->getRates($record, $options))
        ]);
    }

    /**
     * @param array $record
     * @param TransformOptions $options
     *
     * @return array
     */
    protected function getRates(array $record, TransformOptions $options): array
    {
        switch ($options->getPriceReturnType()) {

            case TransformOptions::PRICE_RETURN_TYPE_BASE:
                return $record['baseRates'];
                break;

            default:
            case TransformOptions::PRICE_RETURN_TYPE_TOTAL:
                return $record['rates'];
                break;

        }
    }

}