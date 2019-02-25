<?php

namespace Los;

class LosOptions
{

    private $defaultMinStay = 0;
    private $defaultMaxStay = 30;

    /**
     * @return int
     */
    public function getDefaultMinStay(): int
    {
        return $this->defaultMinStay;
    }

    /**
     * @param int $defaultMinStay
     */
    public function setDefaultMinStay(int $defaultMinStay)
    {
        $this->defaultMinStay = $defaultMinStay;
    }

    /**
     * @return int
     */
    public function getDefaultMaxStay(): int
    {
        return $this->defaultMaxStay;
    }

    /**
     * @param int $defaultMaxStay
     */
    public function setDefaultMaxStay(int $defaultMaxStay)
    {
        $this->defaultMaxStay = $defaultMaxStay;
    }

}