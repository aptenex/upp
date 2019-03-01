<?php

namespace Los\Transformer;

/**
 * @package Los\Transformer
 */
class BookingComRecordTransformer extends ArrayRecordTransformer
{

    public function generateLosRecordString($record, TransformOptions $options): string
    {
        return vsprintf('%s,%s,%s,%s,%s,%s', [
            $record['date'],
            $record['guest'],
            $options->getBcomRoomId(),
            $options->getBcomRateId(),
            $options->getCurrency(),
            implode(',', $record['rates'])
        ]);
    }

}