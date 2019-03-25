<?php

namespace Aptenex\Upp\Los\Transformer;

use Aptenex\Upp\Los\LosRecords;

interface RecordTransformerInterface
{

    /**
     * @param LosRecords $records
     * @param TransformOptions|null $options
     *
     * @return mixed
     */
    public function transform(LosRecords $records, TransformOptions $options);

    /**
     * @param $record
     * @param TransformOptions $options
     * @return string
     */
    public function generateLosRecordString($record, TransformOptions $options): string;

}