<?php

namespace Aptenex\Upp\Models;

use Money\Money;

class AvailabilityExternal extends Availability
{

    /**
     * @var array
     */
    protected $__origin;

    /**
     * @return array
     */
    public function getOrigin()
    {
        return $this->__origin;
    }

    /**
     * @param array $_origin
     */
    public function setOrigin($_origin)
    {
        $this->__origin = $_origin;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        $data = parent::__toArray();

        return array_merge($data, [
            'origin' => $this->getOrigin()
        ]);
    }

    /**
     * @param $data
     *
     * @return
     */
    public function fromArray($data)
    {
        parent::fromArray($data);
    }

}