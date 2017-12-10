<?php

namespace Aptenex\Upp\Parser\Structure;

use Aptenex\Upp\Parser\ExternalConfig\ExternalCommandDirector;

class StructureOptions
{

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
    public function hasExternalCommandDirector()
    {
        return $this->externalCommandDirector instanceof ExternalCommandDirector;
    }

}