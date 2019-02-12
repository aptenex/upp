<?php

namespace Aptenex\Upp\Models;

use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Exception\ErrorHandler;
use Money\Money;
use Aptenex\Upp\Context\PricingContext;

class Availability extends Price
{

    /**
     * @var boolean
     */
    protected $isAvailable;

    /**
     * @var boolean
     */
    protected $isPriced;

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @var ErrorHandler
     */
    protected $errors = [];

    public function __construct(PricingContext $contextUsed)
    {
        parent::__construct($contextUsed);
        $this->disableSplitDetails();
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return $this->isAvailable;
    }

    /**
     * @param bool $isAvailable
     */
    public function setIsAvailable($isAvailable)
    {
        $this->isAvailable = $isAvailable;
    }

    /**
     * @return bool
     */
    public function isPriced()
    {
        return $this->isPriced;
    }

    /**
     * @param bool $isPriced
     */
    public function setIsPriced($isPriced)
    {
        $this->isPriced = $isPriced;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param array $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    /**
     * @param $message
     */
    public function addMessage($message)
    {
        $this->messages[] = $message;
    }
	


    /**
     * @return bool
     */
    public function hasMessages()
    {
        return !empty($this->messages);
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        $data = $this->getCurrency() ? parent::__toArray() : [];

        return array_merge($data, [
            'isAvailable' => $this->isAvailable(),
            'isPriced'    => $this->isPriced(),
            'errors'      => $this->getErrors()->__toArray(),
            'messages'    => $this->getMessages(),
        ]);
    }
	
	/**
	 * @param $data
	 * @return Price|void
	 */
    public function fromArray($data)
    {
        parent::fromArray($data);

        if (isset($data['bookable'])) {
            $this->setIsAvailable($data['bookable']);
        }

        if (isset($data['bookable'])) {
            $this->setIsPriced($data['bookable']);
        }
        return $this;
    }
	
	/**
	 * @param FinalPrice $price
	 * @return Availability
	 */
    public static function fromFinalPrice(FinalPrice $price){
		$so = serialize( $price );
		$so = preg_replace('/^O:34:"Aptenex\\\\Upp\\\\Calculation\\\\FinalPrice":/','O:31:"Aptenex\Upp\Models\Availability":',$so);
		// Because nothing can go wrong.
		return unserialize($so);
	}

}