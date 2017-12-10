<?php

namespace Aptenex\Upp\Parser;

use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\Structure\PricingConfig;
use Aptenex\Upp\Parser\Structure\CurrencyConfig;
use Aptenex\Upp\Exception\InvalidPricingConfigException;

class CurrencyConfigParser
{

    /**
     * @var PricingConfigParser
     */
    private $pricingConfigParser;

    /**
     * @param PricingConfigParser $pricingConfigParser
     */
    public function __construct(PricingConfigParser $pricingConfigParser)
    {
        $this->pricingConfigParser = $pricingConfigParser;
    }

    public function parse(PricingConfig $config, array $currencyConfigs)
    {
        if (!is_array($currencyConfigs)) {
            throw new InvalidPricingConfigException("The property pricing config is invalid - data object should be an array");
        }

        $pc = [];
        $resolver = $this->pricingConfigParser->getResolver();

        foreach($currencyConfigs as $rawConfig) {
            $c = new CurrencyConfig();

            $c->setDefaults((new DefaultsParser())->parse(ArrayAccess::get('defaults', $rawConfig, [])));

            $c->setCurrency(ArrayAccess::getOrException(
                'currency',
                $rawConfig,
                InvalidPricingConfigException::class,
                'Could not locate the data.currency field'
            ));

            $taxes = ArrayAccess::get('taxes', $rawConfig, null);

            if ($resolver->isMixin($taxes)) {
                $mixin = $resolver->parseAndResolveMixin($taxes);
                $c->setTaxes((new TaxesParser())->parse((array) $mixin));
            } else if (is_array($taxes)) {
                $c->setTaxes((new TaxesParser())->parse($taxes));
            } else {
                $c->setTaxes([]);
            }

            $c->setPeriods((new PeriodsParser())->parse(ArrayAccess::get('periods', $rawConfig, [])));
            $c->setModifiers((new ModifiersParser())->parse(ArrayAccess::get('modifiers', $rawConfig, [])));

            $pc[strtoupper($c->getCurrency())] = $c;
        }

        $config->setCurrencyConfigs($pc);
    }

}