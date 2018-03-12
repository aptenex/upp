<?php

namespace Parser\ExternalConfig;

use Aptenex\Upp\Parser\ExternalConfig\ExternalCommandInterface;
use Aptenex\Upp\Parser\Structure\PricingConfig;

class ConfigOverrideCommand implements ExternalCommandInterface
{

    /**
     * @var array
     */
    private $overrides;

    /**
     * @param array $overrides
     */
    public function __construct(array $overrides = [])
    {
        $this->overrides = $overrides;
    }

    /**
     * @param PricingConfig $config
     */
    public function apply(PricingConfig $config)
    {
        foreach ($config->getCurrencyConfigs() as $cConfig) {
            foreach($this->overrides as $key => $value) {
                $setter = sprintf('set%s', ucfirst($key));
                if (method_exists($cConfig, $setter)) {
                    $cConfig->$setter($value);
                }
            }
        }
    }

}