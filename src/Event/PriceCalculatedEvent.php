<?php

namespace Aptenex\Upp\Event;

use Aptenex\Upp\Models\Price;
use Symfony\Component\EventDispatcher\Event;

class PriceCalculatedEvent extends Event {
	
	const PRICE_CALCULATED = 'upp.price.calculated';
	
	protected $price;
	
	public function __construct(Price $price)
	{
		$this->price = $price;
	}
	
	public function getPrice()
	{
		return $this->price;
	}
	
}