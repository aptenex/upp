<?php

namespace Aptenex\Upp\Builder;

class BuildResult
{

    /**
     * @var array
     */
    private $finalConfig;

    /**
     * @var array
     */
    private $propertyConfig;

    /**
     * @var array
     */
    private $taxesAddedByCurrencyMap;

    /**
     * @var array
     */
    private $modifiersAddedByCurrencyMap;

    /**
     * BuildResult constructor.
     * @param array $finalConfig
     * @param array $propertyConfig
     * @param array $taxesAddedByCurrencyMap
     * @param array $modifiersAddedByCurrencyMap
     */
    public function __construct(array $finalConfig, array $propertyConfig, array $taxesAddedByCurrencyMap = [], array $modifiersAddedByCurrencyMap = [])
    {
        $this->finalConfig = $finalConfig;
        $this->propertyConfig = $propertyConfig;
        $this->taxesAddedByCurrencyMap = $taxesAddedByCurrencyMap;
        $this->modifiersAddedByCurrencyMap = $modifiersAddedByCurrencyMap;
    }

    /**
     * @return array
     */
    public function getFinalConfig(): array
    {
        return $this->finalConfig;
    }

    /**
     * @return array
     */
    public function getPropertyConfig(): array
    {
        return $this->propertyConfig;
    }

    /**
     * @return array
     */
    public function getTaxesAddedByCurrencyMap(): array
    {
        return $this->taxesAddedByCurrencyMap;
    }

    /**
     * @return array
     */
    public function getModifiersAddedByCurrencyMap(): array
    {
        return $this->modifiersAddedByCurrencyMap;
    }

    /**
     * @return int
     */
    public function getTotalMergedItems(): int
    {
        $count = 0;

        foreach($this->getTaxesAddedByCurrencyMap() as $currency => $items) {
            $count += \count($items);
        }

        foreach($this->getModifiersAddedByCurrencyMap() as $currency => $items) {
            $count += \count($items);
        }

        return $count;
    }

}