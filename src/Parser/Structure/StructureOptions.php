<?php

namespace Aptenex\Upp\Parser\Structure;

use Aptenex\Upp\Parser\ExternalConfig\ExternalCommandDirector;

class StructureOptions
{

    /**
     * Not recommended when running actual pricing calculations but useful when transforming the data
     *
     * @var bool
     */
    private $expandNestedPeriods = false;

    /**
     * @var ExternalCommandDirector|null
     */
    private $externalCommandDirector;

    /**
     * If a value is passed here, when parsing the modifiers it will exclude any that do not meet this channel
     *
     * @var string|null
     */
    private $distributionChannel = null;

    /**
     * @return ExternalCommandDirector|null
     */
    public function getExternalCommandDirector()
    {
        return $this->externalCommandDirector;
    }

    /**
     * @param ExternalCommandDirector|null $externalCommandDirector
     */
    public function setExternalCommandDirector($externalCommandDirector)
    {
        $this->externalCommandDirector = $externalCommandDirector;
    }

    /**
     * @return bool
     */
    public function hasExternalCommandDirector(): bool
    {
        return $this->externalCommandDirector instanceof ExternalCommandDirector;
    }

    /**
     * @return bool
     */
    public function isExpandNestedPeriods(): bool
    {
        return $this->expandNestedPeriods;
    }

    /**
     * @param bool $expandNestedPeriods
     */
    public function setExpandNestedPeriods(bool $expandNestedPeriods)
    {
        $this->expandNestedPeriods = $expandNestedPeriods;
    }

    /**
     * @return null|string
     */
    public function getDistributionChannel(): ?string
    {
        return  strtolower($this->distributionChannel);
    }

    /**
     * @param null|string $distributionChannel
     */
    public function setDistributionChannel(?string $distributionChannel): void
    {
        $this->distributionChannel =  strtolower($distributionChannel);
    }

    /**
     * @return bool
     */
    public function hasDistributionChannel(): bool
    {
        return $this->distributionChannel !== null;
    }

}