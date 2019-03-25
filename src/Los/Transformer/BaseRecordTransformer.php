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
            implode(',', $record['rates'])
        ]);
    }

}