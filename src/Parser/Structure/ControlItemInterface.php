<?php

namespace Aptenex\Upp\Parser\Structure;

interface ControlItemInterface
{

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     */
    public function setDescription($description);

    /**
     * @return int
     */
    public function getPriority();

    /**
     * @param int $priority
     */
    public function setPriority($priority);

    /**
     * @return string
     */
    public function getConditionOperand();
    /**
     * @param string $conditionOperand
     */
    public function setConditionOperand($conditionOperand);

    /**
     * @return Condition[]
     */
    public function getConditions();

    /**
     * @param Condition[] $conditions
     */
    public function setConditions($conditions);

    /**
     * @return Rate
     */
    public function getRate();

    /**
     * @param Rate $rate
     */
    public function setRate($rate);

}