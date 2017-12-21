<?php

namespace Aptenex\Upp\Models;

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
     * @var array
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
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    /**
     * @param $error
     */
    public function addError($error)
    {
        $this->errors[] = $error;
    }

    /**
     * @return bool
     */
    public function hasMessages()
    {
        return !empty($this->messages);
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        $data = parent::__toArray();

        return array_merge($data, [
            'isAvailable' => $this->isAvailable(),
            'isPriced'    => $this->isPriced(),
            'errors'      => $this->getErrors(),
            'messages'    => $this->getMessages(),
        ]);
    }

    /**
     * @param $data
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
    }

}