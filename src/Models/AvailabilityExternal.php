<?php

namespace Aptenex\Upp\Models;

use Aptenex\Upp\Calculation\AdjustmentAmount;
use Aptenex\Upp\Calculation\Stay;
use Money\Money;
use Aptenex\Upp\Util\MoneyUtils;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Parser\Structure\Period;
use Aptenex\Upp\Calculation\SplitAmount\GuestSplitOverview;

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

}