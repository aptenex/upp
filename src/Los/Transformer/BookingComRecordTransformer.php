<?php

namespace Aptenex\Upp\Los\Transformer;

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
            $options->getTargetCurrency() ? $options->getTargetCurrency()->getCode() : $record['currency'], // We know we've done a conversion.
           implode(',',  $this->getRates($record, $options))
        ]);
    }

}