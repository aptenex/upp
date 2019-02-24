<?php

namespace Aptenex\Upp\Parser\Structure;

use Aptenex\Upp\Parser\ExternalConfig\ExternalCommandDirector;

class StructureOptions
{

    /**
     * Not reccommended when running actual pricing calculations but useful when transforming the data
     *
     * @var bool
     */
    private $expandNestedPeriods = false;

    /**
     * @var ExternalCommandDirector|null
     */
    private $externalCommandDirector;

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

}