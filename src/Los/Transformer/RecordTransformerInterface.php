<?php

namespace Los\Transformer;

use Los\LosRecords;

interface RecordTransformerInterface
{

    /**
     * @param LosRecords $records
     * @return mixed
     */
    public function transform(LosRecords $records);

}