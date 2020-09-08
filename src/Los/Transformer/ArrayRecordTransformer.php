<?php /** @noinspection SlowArrayOperationsInLoopInspection */
    
    namespace Aptenex\Upp\Los\Transformer;
    
    use Aptenex\Upp\Exception\CannotGenerateLosException;
    use Aptenex\Upp\Los\LosRecords;
    use Money\Currency;
    
    /**
     * This array -> record transformer mimics Airbnb's format near identical just with multiple currencies
     *
     * @package Los\Transformer
     */
    class ArrayRecordTransformer extends BaseRecordTransformer
    {
        
        /**
         * @param LosRecords       $records
         * @param TransformOptions $options
         *
         * @return array Format is [ los_string, los_string, los_string ]
         */
        public function transform(LosRecords $records, TransformOptions $options): array
        {
            if (empty($records->getRecords())) {
                return [];
            }
            
            $data = [];
            
            // We do this so that we know if we need to do any conversion using the Exchange.
            $options->setSourceCurrency(new Currency($records->getCurrency()));
            
            $computedEmptyHash = null;
            
            foreach ($records->getRecords() as $date => $dateSet) {
                // We need a lookahead to merge the first guest count if a few guest counts have the same rate
                // this is because there is no way to compare the first hash to the previous hash on the first one
                $firstLookahead           = $dateSet[1] ?? null;
                $previousSingleRecord     = null;
                $maxGuestCountForSameHash = 0;
                $guestEntries             = count($dateSet);
                $optimisedArrivalDateRates = [];
                
                // Array  which stores optimised guest rate prices. (no duplicate prices)
                // We reverse the counts, bebcause the intention here is to only take the HIGHEST
                // rateHash per guest count. The reason is we don't need to send, 1,2,3 prices, if the rate for
                // 4 guests is all the same. We only need to provide the pricing for four. So, in turn we can stop
                // execution if we know the 3 or 2 price is the same as the last hash.
                foreach (array_reverse($dateSet) as $index => $singleRecord) {
                    
                    if ($index === 0 && $computedEmptyHash === null) {
                        // We need to compute the hash of all 0's in a string, but we do it here as this transformer
                        // does not know the MAXIMUM stay length so we can just get it here on the very first instance
                        $computedEmptyHash = sha1(implode(',', array_fill(0, count($singleRecord['rates']), 0)));
                    }
                    
                    // Should we skip empty records? Some might want to. Some might not.... (ie Booking.com does not like to)
                    if ($singleRecord['rateHash'] === $computedEmptyHash && $options->isSkipEmptyLosRecordsFromTransformation()) {
                        continue;
                    }
                    
                    if(($options->isRestrictSameGuestRatesToSingleOccupancy() && !isset($optimisedArrivalDateRates[$singleRecord['rateHash']])) || !$options->isRestrictSameGuestRatesToSingleOccupancy()) {
                        $optimisedArrivalDateRates[$singleRecord['rateHash']][$singleRecord['guest']] = $this->generateLosRecordString($singleRecord, $options);
                    }
                    
                }
                
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $p = array_values( $optimisedArrivalDateRates);
                $data = array_merge($data, ... array_reverse((array_map(static function($item){
                    return array_reverse($item);
                }, $p))));
            }
            return $data;
        }
    }