<?php

namespace Tests;

use Aptenex\Upp\Calculation\Condition\Condition;
use Aptenex\Upp\Calculation\ControlItem\Modifier;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Calculation\Pricing\RatePerConditionalUnitCalculator;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Parser\ConditionsParser;
use PHPUnit\Framework\TestCase;

class RatePerConditionalUnitCalculatorTest extends TestCase
{

    public function testGuestConditionCorrectUnitsDetermined()
    {

        $rpcu = new RatePerConditionalUnitCalculator();

        $context = new PricingContext();
        $context->setGuests(2);

        $modifier = new Modifier($this->createMock(FinalPrice::class));

        $conditions = (new ConditionsParser())->parse([
            [
                'type' => 'guests',
                'modifyRatePerUnit' => true,
            ]
        ]);

        $condition = $conditions[0];

        $priceCon = new Condition();
        $priceCon->setConditionConfig($condition);

        $result = $rpcu->determineUnits(
            $context,
            $priceCon,
            $modifier
        );

        $this->assertSame(2, $result->getUnits());
    }

}