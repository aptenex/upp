<?php

namespace Aptenex\Upp\Parser;

use Aptenex\Upp\Calculation\AdjustmentAmount;
use Aptenex\Upp\Exception\InvalidPricingConfigException;
use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\Structure\Condition;
use Aptenex\Upp\Parser\Structure\Modifier;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Parser\Structure\SplitMethod;
use Aptenex\Upp\Parser\Structure\StructureOptions;

class ModifiersParser
{

    /**
     * @param array $modifiersArray
     * @param StructureOptions $options
     *
     * @return Modifier[]
     */
    public function parse(array $modifiersArray, StructureOptions $options): array
    {
        $m = [];

        foreach($modifiersArray as $index => $modifier) {
            $mo =  $this->parseModifier($modifier, $index);

            if (
                $options->hasDistributionChannel() &&
                $this->matchesDistributionChannel($mo, $options->getDistributionChannel()) === false
            ) {
                continue;
            }

            $m[] = $mo;
        }

        return $m;
    }

    /**
     * @param Modifier $modifier
     * @param null|string $distributionChannel
     * @return bool
     */
    private function matchesDistributionChannel(Modifier $modifier, ?string $distributionChannel): bool
    {
        foreach($modifier->getConditions() as $condition) {

            /** @var Condition\DistributionCondition $condition */
            if (
                $condition->getType() === Condition::TYPE_DISTRIBUTION &&
                \in_array($distributionChannel, $condition->getChannels(), true)
            ) {
                return true;
            }
        }

        return false;
    }

    private function parseModifier($modifierData, $index): Modifier
    {
        $m = new Modifier();

        $m->setType(ArrayAccess::get('type', $modifierData, Modifier::TYPE_MODIFIER));
        $m->setHidden(ArrayAccess::get('hidden', $modifierData, false));
        $m->setPriceGroup(ArrayAccess::get('priceGroup', $modifierData, AdjustmentAmount::PRICE_GROUP_TOTAL));
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