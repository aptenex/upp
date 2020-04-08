<?php

namespace Aptenex\Upp\Parser;

use Aptenex\Upp\Parser\Structure\PricingConfig;

class BaseChildParser
{

    /**
     * @var PricingConfig
     */
    private $config;

    public function __construct(PricingConfig $config = null)
    {
        $this->config = $config ?: new PricingConfig('', '', '', [], []);
    }

    public function getConfig(): PricingConfig
    {
        return $this->config;
    }

}