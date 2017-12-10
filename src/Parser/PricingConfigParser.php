<?php

namespace Aptenex\Upp\Parser;

use Aptenex\Upp\Exception\BaseException;
use Aptenex\Upp\Exception\InvalidPricingConfigException;
use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\Resolver\ResolverInterface;
use Aptenex\Upp\Parser\Structure\PricingConfig;

class PricingConfigParser
{

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @param ResolverInterface $resolver
     */
    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @param array $data
     *
     * @throws BaseException
     *
     * @return PricingConfig|null
     */
    public function parsePricingConfig(array $data)
    {
        if (is_null($data) || empty($data)) {
            throw new InvalidPricingConfigException("No pricing configuration set for this property");
        }

        $pc = new PricingConfig(
            ArrayAccess::getOrException('name', $data, InvalidPricingConfigException::class, "The 'name' parameter is not specified"),
            ArrayAccess::getOrException('schema', $data, InvalidPricingConfigException::class, "The 'schema' parameter is not specified"),
            ArrayAccess::getOrException('version', $data, InvalidPricingConfigException::class, "The 'version' parameter is not specified"),
            ArrayAccess::getOrException('meta', $data, InvalidPricingConfigException::class, "The 'meta' parameter is not specified"),
            $data
        );

        $ccp = new CurrencyConfigParser($this);

        $ccp->parse($pc, ArrayAccess::getOrException('data', $data, InvalidPricingConfigException::class, "No 'data' object exists"));

        return $pc;
    }

    /**
     * @return ResolverInterface
     */
    public function getResolver()
    {
        return $this->resolver;
    }
    
}