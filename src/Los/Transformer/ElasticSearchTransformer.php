<?php

namespace Aptenex\Upp\Los\Transformer;

/**
 * @package Los\Transformer
 */
class ElasticSearchTransformer extends ArrayRecordTransformer
{

    public function generateLosRecordString($record, TransformOptions $options): string
    {
        return vsprintf(
            '%s,%s',
            [
                $record['guest'],
                implode(',', $this->getRates($record, $options)),
            ]
        );
    }

}