<?php

namespace Aptenex\Upp\Parser;

use Aptenex\Upp\Exception\InvalidPricingConfigException;
use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Parser\Structure\Period;

class PeriodsParser extends BaseChildParser
{

    /**
     * @param array $periodsArray
     * @return Period[]
     */
    public function parse(array $periodsArray)
    {
        $p = [];

        foreach($periodsArray as $index => $period) {
            $p[] = $this->parsePeriod($period, $index);
        }

        return $p;
    }

    private function parsePeriod($periodData, $index)
    {
        $p = new Period();

        $p->setDescription(ArrayAccess::get('description', $periodData));

        $p->setPriority(ArrayAccess::get('priority', $periodData));

        $p->setBookableType(ArrayAccess::get('bookableType', $periodData, null)); // As this uses defaults

        $p->setConditionOperand(ArrayAccess::getViaWhitelist(
            'conditionOperand',
            $periodData,
            Operand::OP_OR,
            Operand::getConditionalList()
        ));

        $p->setConditions((new ConditionsParser($this->getConfig()))->parse(ArrayAccess::get('conditions', $periodData, [])));

        $p->setRate((new RateParser($this->getConfig()))->parse(ArrayAccess::getOrException(
            'rate',
            $periodData,
            InvalidPricingConfigException::class,
            sprintf("No 'rate' parameter is set for the period at index %s", $index)
        )));

        $p->setMinimumNights(ArrayAccess::get('minimumNights', $periodData, null));

        return $p;
    }

}