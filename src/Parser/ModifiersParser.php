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

class ModifiersParser extends BaseChildParser
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
                $options->hasDistributionChannel() && // has the option
                $this->getDistributionCondition($mo) !== null // this modifier contains the condition
            ) {
                if ($this->matchesDistributionChannel($mo, $options->getDistributionChannel())) {
                    // We need to remove this condition from the modifier
                    $mo->setConditions(array_filter($mo->getConditions(), function ($item) {
                        /** @var Condition $item */
                        return $item->getType() !== Condition::TYPE_DISTRIBUTION;
                    }));
                } else {
                    continue;
                }
            }

            $m[] = $mo;
        }

        return $m;
    }

    /**
     * @param Modifier $modifier
     * @return
     */
    protected function getDistributionCondition(Modifier $modifier): ?Condition\DistributionCondition
    {
        foreach($modifier->getConditions() as $condition) {
            if ($condition->getType() === Condition::TYPE_DISTRIBUTION) {
                /** @var Condition\DistributionCondition $condition */
                return $condition;
            }
        }

        return null;
    }

    /**
     * @param Modifier $modifier
     * @param null|string $distributionChannel
     * @return bool
     */
    protected function matchesDistributionChannel(Modifier $modifier, ?string $distributionChannel): bool
    {
        foreach($modifier->getConditions() as $condition) {

            /** @var Condition\DistributionCondition $condition */
            if (
                $condition->getType() === Condition::TYPE_DISTRIBUTION &&
                \in_array(  strtolower($distributionChannel), $condition->getChannels(), true)
            ) {
                return true;
            }
        }

        return false;
    }

    protected function parseModifier($modifierData, $index): Modifier
    {
        $m = new Modifier();

        $m->setType(ArrayAccess::get('type', $modifierData, Modifier::TYPE_MODIFIER));
        $m->setHidden(ArrayAccess::get('hidden', $modifierData, false));
        $m->setPriceGroup(ArrayAccess::get('priceGroup', $modifierData, AdjustmentAmount::PRICE_GROUP_TOTAL));
        $m->setSplitMethod(ArrayAccess::get('splitMethod', $modifierData, SplitMethod::ON_TOTAL));
        $m->setId(ArrayAccess::get('id', $modifierData, md5(random_bytes(10))));
        $m->setDescription(ArrayAccess::getOrException(
            'description',
            $modifierData,
            InvalidPricingConfigException::class,
            sprintf("The 'description' parameter is not set for the period at index %s", $index)
        ));

        $m->setPriority(ArrayAccess::get('priority', $modifierData, 0));

        $m->setConditionOperand(ArrayAccess::getViaWhitelist(
            'conditionOperand',
            $modifierData,
            Operand::OP_OR,
            Operand::getConditionalList()
        ));

        $m->setConditions((new ConditionsParser($this->getConfig()))->parse(ArrayAccess::get('conditions', $modifierData, [])));

        $m->setRate((new RateParser($this->getConfig()))->parse(ArrayAccess::getOrException(
            'rate',
            $modifierData,
            InvalidPricingConfigException::class,
            sprintf("No 'rate' parameter is set for the period at index %s", $index)
        )));

        return $m;
    }

}