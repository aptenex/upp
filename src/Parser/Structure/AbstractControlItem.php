<?php

namespace Aptenex\Upp\Parser\Structure;

use Aptenex\Upp\Parser\Structure\Condition\DateCondition;
use Aptenex\Upp\Util\ArrayUtils;
use Symfony\Component\Validator\Constraints\Valid;

abstract class AbstractControlItem implements ControlItemInterface
{

    /**
     * @var int
     */
    private static $counter = 0;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var int
     */
    protected $priority = null;

    /**
     * @var string
     */
    protected $conditionOperand = Operand::OP_OR;

    /**
     * @var Condition[]
     */
    protected $conditions = [];

    /**
     * @Valid()
     *
     * @var Rate
     */
    protected $rate;

    public function __construct()
    {
        self::$counter++;
        $this->id = self::$counter;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return string
     */
    public function getConditionOperand()
    {
        return $this->conditionOperand;
    }

    /**
     * @param string $conditionOperand
     */
    public function setConditionOperand($conditionOperand)
    {
        $this->conditionOperand = $conditionOperand;
    }

    /**
     * @return Condition[]
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param Condition[] $conditions
     */
    public function setConditions($conditions)
    {
        $this->conditions = $conditions;
    }

    /**
     * @return Rate
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * @param Rate $rate
     */
    public function setRate($rate)
    {
        $this->rate = $rate;
    }

    /**
     * @return DateCondition|null
     */
    public function getDateCondition()
    {
        foreach($this->getConditions() as $con) {
            if ($con->getType() === Condition::TYPE_DATE) {
                return $con;
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return [
            'id' => $this->getId(),
            'description' => $this->getDescription(),
            'priority' => $this->getPriority(),
            'conditionOperand' => $this->getConditionOperand(),
            'conditions' => ArrayUtils::convertStructureObjectToArray($this->getConditions()),
            'rate' => $this->getRate() ? $this->getRate()->__toArray() : null
        ];
    }

}