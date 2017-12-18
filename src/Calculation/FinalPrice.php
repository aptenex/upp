<?php

namespace Aptenex\Upp\Calculation;


use Aptenex\Upp\Models\Price;
use Money\Money;
use Aptenex\Upp\Helper\LanguageTools;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Parser\Structure\PricingConfig;
use Aptenex\Upp\Exception\InvalidPricingConfigException;

class FinalPrice extends Price
{

    /**
     * @var PricingConfig
     */
    private $configUsed;

    /**
     * FinalPrice constructor.
     * @param PricingContext $contextUsed
     * @param PricingConfig $configUsed
     */
    public function __construct(PricingContext $contextUsed, PricingConfig $configUsed)
    {
        parent::__construct($contextUsed);
        $this->configUsed = $configUsed;

        $this->validateCurrency();
    }

    /**
     * @throws InvalidPricingConfigException
     */
    private function validateCurrency()
    {
        if (!array_key_exists($this->getCurrency(), $this->getConfigUsed()->getCurrencyConfigs())) {
            throw new InvalidPricingConfigException(LanguageTools::trans('CURRENCY_NOT_CONFIGURED'));
        }
    }

    /**
     * @return \Aptenex\Upp\Parser\Structure\CurrencyConfig
     */
    public function getCurrencyConfigUsed()
    {
        return $this->getConfigUsed()->getCurrencyConfigs()[$this->getCurrency()];
    }

    /**
     * @return PricingConfig
     */
    public function getConfigUsed()
    {
        return $this->configUsed;
    }



}