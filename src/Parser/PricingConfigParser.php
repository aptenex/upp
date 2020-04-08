<?php

namespace Aptenex\Upp\Parser;

use Aptenex\Upp\Exception\BaseException;
use Aptenex\Upp\Exception\InvalidPricingConfigException;
use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\Resolver\ResolverInterface;
use Aptenex\Upp\Parser\Structure\PricingConfig;
use Aptenex\Upp\Parser\Structure\StructureOptions;

class PricingConfigParser
{

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var StructureOptions|null
     */
    private $options;

    /**
     * @param ResolverInterface $resolver
     * @param StructureOptions|null $options
     */
    public function __construct(ResolverInterface $resolver, StructureOptions $options = null)
    {
        $this->resolver = $resolver;

        if ($options === null) {
            $options = new StructureOptions();
        }

        $this->options = $options;
    }

    /**
     * @param array $data
     *
     * @return PricingConfig|null
     * @throws InvalidPricingConfigException
     */
    public function parsePricingConfig(array $data)
    {
        if ($data === null || empty($data)) {
            throw new InvalidPricingConfigException('No pricing configuration set for this property');
        }

        $pc = new PricingConfig(
            ArrayAccess::getOrException('name', $data, InvalidPricingConfigException::class, "The 'name' parameter is not specified"),
            ArrayAccess::getOrException('schema', $data, InvalidPricingConfigException::class, "The 'schema' parameter is not specified"),
            ArrayAccess::getOrException('version', $data, InvalidPricingConfigException::class, "The 'version' parameter is not specified"),
            ArrayAccess::get('meta', $data, []),
            $data
        );

        $ccp = new CurrencyConfigParser($this, $pc);

        $ccp->parse($pc, ArrayAccess::getOrException('data', $data, InvalidPricingConfigException::class, "No 'data' object exists"));

        return $pc;
    }

    /**
     * @return ResolverInterface
     */
    public function getResolver(): ResolverInterface
    {
        return $this->resolver;
    }

    /**
     * @return StructureOptions
     */
    public function getOptions(): StructureOptions
    {
        return $this->options;
    }



}