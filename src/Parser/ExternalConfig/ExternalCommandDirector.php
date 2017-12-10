<?php

namespace Aptenex\Upp\Parser\ExternalConfig;

use Aptenex\Upp\Parser\Structure\PricingConfig;

class ExternalCommandDirector implements ExternalCommandInterface
{

    /**
     * @var ExternalCommandInterface[]|array
     */
    private $commands;

    /**
     * @param ExternalCommandInterface[] $commands
     */
    public function __construct(array $commands)
    {
        $this->commands = $commands;
    }

    /**
     * @param PricingConfig  $config
     */
    public function apply(PricingConfig $config)
    {
        foreach($this->commands as $command) {
            $command->apply($config);
        }
    }

}