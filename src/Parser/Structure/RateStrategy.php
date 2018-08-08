<?php

namespace Aptenex\Upp\Parser\Structure;

class RateStrategy
{

    /**
     * @var PartialWeekAlteration
     */
    private $partialWeekAlteration;

    /**
     * @var ExtraNightsAlteration
     */
    private $extraNightsAlteration;

    /**
     * @var ExtraMonthsAlteration
     */
    private $extraMonthsAlteration;

    /**
     * @var DaysOfWeekAlteration
     */
    private $daysOfWeekAlteration;

    /**
     * @return PartialWeekAlteration
     */
    public function getPartialWeekAlteration()
    {
        return $this->partialWeekAlteration;
    }

    /**
     * @param PartialWeekAlteration $partialWeekAlteration
     */
    public function setPartialWeekAlteration($partialWeekAlteration)
    {
        $this->partialWeekAlteration = $partialWeekAlteration;
    }

    /**
     * @return ExtraNightsAlteration
     */
    public function getExtraNightsAlteration()
    {
        return $this->extraNightsAlteration;
    }

    /**
     * @param ExtraNightsAlteration $extraNightsAlteration
     */
    public function setExtraNightsAlteration($extraNightsAlteration)
    {
        $this->extraNightsAlteration = $extraNightsAlteration;
    }

    /**
     * @return DaysOfWeekAlteration
     */
    public function getDaysOfWeekAlteration()
    {
        return $this->daysOfWeekAlteration;
    }

    /**
     * @param DaysOfWeekAlteration $daysOfWeekAlteration
     */
    public function setDaysOfWeekAlteration($daysOfWeekAlteration)
    {
        $this->daysOfWeekAlteration = $daysOfWeekAlteration;
    }

    /**
     * @return ExtraMonthsAlteration
     */
    public function getExtraMonthsAlteration()
    {
        return $this->extraMonthsAlteration;
    }

    /**
     * @param ExtraMonthsAlteration $extraMonthsAlteration
     */
    public function setExtraMonthsAlteration($extraMonthsAlteration)
    {
        $this->extraMonthsAlteration = $extraMonthsAlteration;
    }

    /**
     * @return PeriodStrategy|null
     */
    public function getActiveStrategy()
    {
        if ($this->getDaysOfWeekAlteration() instanceof DaysOfWeekAlteration) {
            return $this->getDaysOfWeekAlteration();
        }

        if ($this->getExtraNightsAlteration() instanceof ExtraNightsAlteration) {
            return $this->getExtraNightsAlteration();
        }

        if ($this->getExtraMonthsAlteration() instanceof ExtraMonthsAlteration) {
            return $this->getExtraMonthsAlteration();
        }

        if ($this->getPartialWeekAlteration() instanceof PartialWeekAlteration) {
            return $this->getPartialWeekAlteration();
        }

        return null;
    }

    /**
     * @return array
     */
    public function __toArrayOfObjects()
    {
        $s = [];

        if ($this->getDaysOfWeekAlteration() instanceof DaysOfWeekAlteration) {
            $s['daysOfWeekAlteration'] = $this->getDaysOfWeekAlteration();
        }

        if ($this->getExtraNightsAlteration() instanceof ExtraNightsAlteration) {
            $s['extraNightsAlteration'] = $this->getExtraNightsAlteration();
        }

        if ($this->getExtraMonthsAlteration() instanceof ExtraMonthsAlteration) {
            $s['extraMonthsAlteration'] = $this->getExtraMonthsAlteration();
        }

        if ($this->getPartialWeekAlteration() instanceof PartialWeekAlteration) {
            $s['partialWeekAlteration'] = $this->getPartialWeekAlteration();
        }

        return $s;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        $s = [];

        if ($this->getDaysOfWeekAlteration() instanceof DaysOfWeekAlteration) {
            $s['daysOfWeekAlteration'] = $this->getDaysOfWeekAlteration()->__toArray();
        }

        if ($this->getExtraNightsAlteration() instanceof ExtraNightsAlteration) {
            $s['extraNightsAlteration'] = $this->getExtraNightsAlteration()->__toArray();
        }

        if ($this->getExtraMonthsAlteration() instanceof ExtraMonthsAlteration) {
            $s['extraMonthsAlteration'] = $this->getExtraMonthsAlteration()->__toArray();
        }

        if ($this->getPartialWeekAlteration() instanceof PartialWeekAlteration) {
            $s['partialWeekAlteration'] = $this->getPartialWeekAlteration()->__toArray();
        }

        return $s;
    }

}