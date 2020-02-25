<?php

namespace Aptenex\Upp\Parser\ExternalConfig;

use Aptenex\Upp\Parser\Structure\PricingConfig;

class StrategyRemovalCommand implements ExternalCommandInterface
{

    /**
     * @param PricingConfig $config
     */
    public function apply(PricingConfig $config)
    {
        foreach ($config->getCurrencyConfigs() as $cConfig) {
            foreach($cConfig->getPeriods() as $period) {
                $period->getRate()->setStrategy(null);
            }
        }
    }

}