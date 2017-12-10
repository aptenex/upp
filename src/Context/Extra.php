<?php

namespace Aptenex\Upp\Context;

use Aptenex\Upp\Helper\ArrayAccess;
use Doctrine\Common\Annotations\Annotation\Required;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Type;

class Extra
{

    /**
     * @Required()
     * @Length(min="1", max="255")
     *
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @Required()
     * @Type(type="numeric")
     * @Length(min="0", max="999999")
     *
     * @var number
     */
    private $amount;

    /**
     * @var string[]
     */
    private $matches = [];

    /**
     * @param string $name
     * @param string $description
     * @param number $amount
     * @param \string[] $matches
     */
    public function __construct($name, $description, $amount, $matches)
    {
        $this->name = $name;
        $this->description = $description;
        $this->amount = $amount;
        $this->matches = is_null($matches) ? [] : $matches;
    }

    /**
     * @param array $data
     *
     * @return Extra
     */
    public static function initializeFromData($data)
    {
        return new Extra(
            ArrayAccess::get('name', $data),
            ArrayAccess::get('description', $data),
            ArrayAccess::get('amount', $data),
            ArrayAccess::get('matches', $data)
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return number
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return \string[]
     */
    public function getMatches()
    {
        return $this->matches;
    }

}