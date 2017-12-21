<?php

namespace Aptenex\Upp\Event;

use Aptenex\Upp\Models\Availability;
use Aptenex\Upp\Models\Price;
use Symfony\Component\EventDispatcher\Event;

class AvailabilityCalculatedEvent extends Event {
	
	const AVAILABILITY_CALCULATED = 'upp.availability.calculated';
	
	protected $availability;
	
	public function __construct(Availability $availability)
	{
		$this->availability = $availability;
	}
	
	public function getAvailability()
	{
		return $this->availability;
	}
	
}