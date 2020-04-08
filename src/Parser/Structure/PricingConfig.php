<?php

namespace Aptenex\Upp\Parser\Structure;

use Aptenex\Upp\Exception\BaseException;
use Aptenex\Upp\Util\ArrayUtils;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * This is the root level class that will contain all of the parsed schema data
 */
class PricingConfig
{

    public const FLAG_HAS_PER_GUEST_MODIFIER = 'HAS_PER_GUEST_MODIFIER';
    public const FLAG_HAS_PER_GUEST_PERIOD_STRATEGY = 'HAS_PER_GUEST_PERIOD_STRATEGY';

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
     * @var string[]
     */
    private $flags = [];

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
     * @param string $currency
     *
     * @return CurrencyConfig|null
     */
    public function getCurrencyConfig(string $currency): ?CurrencyConfig
    {
        foreach ($this->currencyConfigs as $cc) {
            if (\strtoupper($cc->getCurrency()) === \strtoupper($currency)) {
                return $cc;
            }
        }

        return null;
    }

    /**
     * @param string $currency
     *
     * @return bool
     */
    public function hasCurrencyConfig(string $currency): bool
    {
        return $this->getCurrencyConfig($currency) !== null;
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

    /**
     * @return string[]
     */
    public function getFlags(): array
    {
        return $this->flags;
    }

    /**
     * @param string $flag
     *
     * @return bool
     */
    public function hasFlag(string $flag): bool
    {
        return array_key_exists($flag, $this->flags);
    }

    /**
     * @param string $flag
     * @return array|mixed
     */
    public function getFlag(string $flag)
    {
        return ArrayUtils::getNestedArrayValue($flag, $this->flags, null);
    }

    /**
     * @param string $flag
     * @param mixed $flagData
     */
    public function addFlag(string $flag, $flagData): void
    {
        if ($this->hasFlag($flag)) {
            return;
        }

        $this->flags[$flag] = $flagData;
    }

    /**
     * @param string $flag
     * @param mixed $flagData
     */
    public function setFlag(string $flag, $flagData): void
    {
        $this->flags[$flag] = $flagData;
    }

    /**
     * @return array
     */
    public function __toArray(): array
    {
        $ccs = [];

        foreach ($this->getCurrencyConfigs() as $index => $cc) {
            $ccs[] = $cc->__toArray();
        }

        return [
            'name'    => $this->getName(),
            'version' => $this->getVersion(),
            'schema'  => $this->getSchema(),
            'meta'    => $this->getMeta(),
            'flags'   => $this->getFlags(),
            'data'    => $ccs
        ];
    }

}