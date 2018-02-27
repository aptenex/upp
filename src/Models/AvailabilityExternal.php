<?php

namespace Aptenex\Upp\Models;

use Money\Money;

class AvailabilityExternal extends Availability
{

    /**
     * @var array
     */
    protected $__origin;
    
    /** @var  string */
    protected $bookingUri;
    

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
		$external = [
			'origin' => $this->getOrigin()
		];
		if($this->getBookingUri()){
			$external['bookingUri'] = $this->getBookingUri();
		}
        return array_merge($data, $external);
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
	
	/**
	 * @return mixed
	 */
	public function getBookingUri()
	{
		return $this->bookingUri;
	}
	
	/**
	 * @param mixed $bookingUri
	 */
	public function setBookingUri($bookingUri)
	{
		$this->bookingUri = $bookingUri;
	}
 

}