<?php

namespace Aptenex\Upp\Parser\Structure;

use Aptenex\Upp\Exception\BaseException;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * This is the root level class that will contain all of the parsed schema data
 */
class PricingConfig
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $schema;

    /**
     * @var string
     */
    private $version;

    /**
     * @var array
     */
    private $meta = '';

    /**
     * @Valid()
     *
     * @var CurrencyConfig[]
     */
    private $currencyConfigs = null;

    /**
     * @var array
     */
    private $rawConfig;

    /**
     * @param string $name
     * @param string $schema
     * @param string $version
     * @param array $meta
     * @param array $rawConfig
     */
    public function __construct($name, $schema, $version, $meta, array $rawConfig)
    {
        $this->name = $name;
        $this->schema = $schema;
        $this->version = $version;
        $this->meta = $meta;
        $this->rawConfig = $rawConfig;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @return CurrencyConfig[]
     */
    public function getCurrencyConfigs()
    {
        return $this->currencyConfigs;
    }

    /**
     * @param CurrencyConfig[] $currencyConfigs
     *
     * @throws BaseException
     */
    public function setCurrencyConfigs($currencyConfigs)
    {
        if (is_array($this->currencyConfigs)) {
            throw new BaseException("Cannot set currency configs more than once");
        }

        $this->currencyConfigs = $currencyConfigs;
    }

    /**
     * @return array
     */
    public function getRawConfig()
    {
        return $this->rawConfig;
    }

}