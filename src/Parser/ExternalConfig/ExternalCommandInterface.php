<?php

namespace Aptenex\Upp\Parser\ExternalConfig;

use Aptenex\Upp\Parser\Structure\PricingConfig;

interface ExternalCommandInterface
{

    public function apply(PricingConfig $config);

}