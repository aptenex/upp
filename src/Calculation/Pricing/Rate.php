<?php

namespace Aptenex\Upp\Calculation\Pricing;

class Rate
{

    /**
     * @var \Aptenex\Upp\Parser\Structure\Rate
     */
    private $config;

    /**
     * @param \Aptenex\Upp\Parser\Structure\Rate $config
     */
    public function __construct(\Aptenex\Upp\Parser\Structure\Rate $config)
    {
        $this->config = $config;
    }

}