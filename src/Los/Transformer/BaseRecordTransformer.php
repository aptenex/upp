<?php

namespace Aptenex\Upp\Los\Transformer;

use Aptenex\Upp\Los\LosRecords;
use Aptenex\Upp\Util\MoneyUtils;
use Money\Converter;
use Money\Currencies\ISOCurrencies;
use Money\Exchange;

abstract class BaseRecordTransformer implements RecordTransformerInterface
{
    
    abstract public function transform(LosRecords $records, TransformOptions $options);
    
    public function generateLosRecordString($record, TransformOptions $options): string
    {
        return vsprintf(
            '%s,%s,%s',
            [
                $record['date'],
                $record['guest'],
                implode(',', $this->getRates($record, $options)),
            ]
        );
    }
    
    /**
     * @param array            $record
     * @param TransformOptions $options
     *
     * @return array
     */
    protected function getRates(array $record, TransformOptions $options): array
    {
        switch ($options->getPriceReturnType()) {
            
            case TransformOptions::PRICE_RETURN_TYPE_BASE:
                return $this->convertRates($record['baseRates'], $options);
                break;
            
            default:
            case TransformOptions::PRICE_RETURN_TYPE_TOTAL:
                return $this->convertRates($record['rates'], $options);
                break;
            
        }
    }
    
    /**
     * @param array            $rates
     * @param TransformOptions $options
     * @return array
     */
    public function convertRates(array $rates, TransformOptions $options): array
    {
        
        if ($options->getTargetCurrency() === null || $options->getTargetCurrency()->equals(
                $options->getSourceCurrency()
            )) {
            return $rates;
        }
     
        if ($options->getModifyRatePercentage()) {
            foreach ($rates as &$rate) {
                $rate = MoneyUtils::fromSmallestToNormalized(
                    MoneyUtils::fromString($rate, $options->getSourceCurrency())
                              ->multiply($options->getModifyRatePercentage())
                              ->getAmount(),
                    $options->getSourceCurrency()
                );
            }
            unset($rate);
           
        }
        
        if ( ! $options->getExchange() instanceof Exchange) {
            return $rates;
        }
        $converter = new Converter(new ISOCurrencies(), $options->getExchange());
        
        foreach ($rates as &$rate) {
            $rate = MoneyUtils::fromSmallestToNormalized(
                $converter->convert(
                    MoneyUtils::fromString($rate, $options->getSourceCurrency()),
                    $options->getTargetCurrency()
                )->getAmount(),
                $options->getTargetCurrency()
            );
        }
        unset($rate);
        return $rates;
        
    }
    
}