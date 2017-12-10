<?php

namespace Aptenex\Upp\Transformer;

use Aptenex\Upp\Parser\Structure\PricingConfig;

interface TransformerInterface
{

    /**
     * @param PricingConfig $config
     *
     * @return array
     */
    public function transform(PricingConfig $config);

}