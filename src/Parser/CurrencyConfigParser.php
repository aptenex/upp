<?php

namespace Aptenex\Upp\Parser;

use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\Structure\PricingConfig;
use Aptenex\Upp\Parser\Structure\CurrencyConfig;
use Aptenex\Upp\Exception\InvalidPricingConfigException;
use Aptenex\Upp\Util\ConfigUtils;
use Aptenex\Upp\Util\DateUtils;

class CurrencyConfigParser
{

    /**
     * @var PricingConfig
     */
    private $config;

    /**
     * @var PricingConfigParser
     */
    private $pricingConfigParser;

    /**
     * @param PricingConfigParser $pricingConfigParser
     * @param PricingConfig $config
     */
    public function __construct(PricingConfigParser $pricingConfigParser, PricingConfig $config)
    {
        $this->config = $config;
        $this->pricingConfigParser = $pricingConfigParser;
    }

    public function parse(PricingConfig $config, array $currencyConfigs)
    {
        if (!\is_array($currencyConfigs)) {
            throw new InvalidPricingConfigException('The property pricing config is invalid - data object should be an array');
        }

        $pc = [];
        $resolver = $this->pricingConfigParser->getResolver();

        foreach($currencyConfigs as $rawConfig) {
            $c = new CurrencyConfig();

            $c->setDefaults((new DefaultsParser($this->config))->parse(ArrayAccess::get('defaults', $rawConfig, [])));

            $c->setCurrency(ArrayAccess::getOrException(
                'currency',
                $rawConfig,
                InvalidPricingConfigException::class,
                'Could not locate the data.currency field'
            ));

            $taxes = ArrayAccess::get('taxes', $rawConfig, null);

            if ($resolver->isMixin($taxes)) {
                $mixin = $resolver->parseAndResolveMixin($taxes);
                $c->setTaxes((new TaxesParser($this->config))->parse((array) $mixin));
            } else if (is_array($taxes)) {
                $c->setTaxes((new TaxesParser($this->config))->parse($taxes));
            } else {
                $c->setTaxes([]);
            }

            $rawPeriods = ArrayAccess::get('periods', $rawConfig, []);
            if ($this->pricingConfigParser->getOptions()->isExpandNestedPeriods()) {
                $rawPeriods = ConfigUtils::expandPeriods($rawPeriods);
            }

            $c->setPeriods((new PeriodsParser($this->config))->parse($rawPeriods));
            $c->setModifiers((new ModifiersParser($this->config))->parse(ArrayAccess::get('modifiers', $rawConfig, []), $this->pricingConfigParser->getOptions()));

            $pc[strtoupper($c->getCurrency())] = $c;
        }

        $config->setCurrencyConfigs($pc);
    }

}