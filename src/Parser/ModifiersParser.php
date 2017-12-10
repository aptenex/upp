<?php

namespace Aptenex\Upp\Parser;

use Aptenex\Upp\Exception\InvalidPricingConfigException;
use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\Structure\Modifier;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Parser\Structure\SplitMethod;

class ModifiersParser
{

    /**
     * @param array $modifiersArray
     * @return Modifier[]
     */
    public function parse(array $modifiersArray)
    {
        $m = [];

        foreach($modifiersArray as $index => $modifier) {
            $m[] = $this->parseModifier($modifier, $index);
        }

        return $m;
    }

    private function parseModifier($modifierData, $index)
    {
        $m = new Modifier();

        $m->setType(ArrayAccess::get('type', $modifierData, Modifier::TYPE_MODIFIER));
        $m->setHidden(ArrayAccess::get('hidden', $modifierData, false));
        $m->setSplitMethod(ArrayAccess::get('splitMethod', $modifierData, SplitMethod::ON_TOTAL));

        $m->setDescription(ArrayAccess::getOrException(
            'description',
            $modifierData,
            InvalidPricingConfigException::class,
            sprintf("The 'description' parameter is not set for the period at index %s", $index)
        ));

        $m->setPriority(ArrayAccess::get('priority', $modifierData));

        $m->setConditionOperand(ArrayAccess::getViaWhitelist(
            'conditionOperand',
            $modifierData,
            Operand::OP_OR,
            Operand::getConditionalList()
        ));

        $m->setConditions((new ConditionsParser())->parse(ArrayAccess::get('conditions', $modifierData, [])));

        $m->setRate((new RateParser())->parse(ArrayAccess::getOrException(
            'rate',
            $modifierData,
            InvalidPricingConfigException::class,
            sprintf("No 'rate' parameter is set for the period at index %s", $index)
        )));

        return $m;
    }

}