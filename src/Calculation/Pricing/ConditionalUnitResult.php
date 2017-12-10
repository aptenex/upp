<?php

namespace Aptenex\Upp\Calculation\Pricing;

class ConditionalUnitResult
{

    /**
     * @var int
     */
    private $units = 1;

    /**
     * @var string
     */
    private $unitDescription;

    /**
     * @return int
     */
    public function getUnits()
    {
        return $this->units;
    }

    /**
     * @param int $units
     */
    public function setUnits($units)
    {
        $this->units = $units;
    }

    /**
     * @return string
     */
    public function getUnitDescription()
    {
        return $this->unitDescription;
    }

    /**
     * @param string $unitDescription
     */
    public function setUnitDescription($unitDescription)
    {
        $this->unitDescription = $unitDescription;
    }

}