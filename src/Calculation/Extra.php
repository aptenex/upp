<?php

namespace Aptenex\Upp\Calculation;

class Extra
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var number
     */
    private $amount;

    /**
     * @var string[]
     */
    private $matches = [];

    /**
     * @param \Aptenex\Upp\Context\Extra $extra
     *
     * @return Extra
     */
    public static function createFromContextExtra(\Aptenex\Upp\Context\Extra $extra)
    {
        $e = new self;

        $e->setName($extra->getName());
        $e->setDescription($extra->getDescription());
        $e->setAmount($extra->getAmount());
        $e->setMatches($extra->getMatches());

        return $e;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return number
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param number $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return \string[]
     */
    public function getMatches()
    {
        return $this->matches;
    }

    /**
     * @param \string[] $matches
     */
    public function setMatches($matches)
    {
        $this->matches = $matches;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return [
            'name'        => $this->getName(),
            'description' => $this->getDescription(),
            'amount'      => $this->getAmount(),
            'matches'     => $this->getMatches()
        ];
    }

}