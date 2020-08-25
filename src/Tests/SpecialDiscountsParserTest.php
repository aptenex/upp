<?php

namespace Tests;

use Aptenex\Upp\Parser\Structure\PricingConfig;
use Aptenex\Upp\Transformer\SpecialDiscountTransformer;
use PHPUnit\Framework\TestCase;
use Aptenex\Upp\Parser\ModifiersParser;
use Aptenex\Upp\Parser\Structure\StructureOptions;

class SpecialDiscountsParserTest extends TestCase
{

    public function testValidDiscountsParsed(): void
    {
        $specialDiscounts = [
            [
                'type' => 'discount',
                'description' => 'early bird',
                'priceGroup' => 'total',
                'rate' => [
                    'type' => 'nightly',
                    'calculationMethod' => 'percentage',
                    'calculationOperand' => 'subtraction',
                    'amount' => 0.10
                ],
                'conditions' => [
                    [
                        'type' => 'date',
                        'startDate' => '2019-01-01',
                        'endDate' => '2019-12-01'
                    ],
                    [
                        'type' => 'booking_days',
                        'minimum' => 180,
                        'maximum' => null
                    ]
                ]
            ],
            [
                'type' => 'discount',
                'description' => 'last minute discount',
                'priceGroup' => 'total',
                'rate' => [
                    'type' => 'nightly',
                    'calculationMethod' => 'percentage',
                    'calculationOperand' => 'subtraction',
                    'amount' => 0.10
                ],
                'conditions' => [
                    [
                        'type' => 'date',
                        'startDate' => '2019-01-01',
                        'endDate' => '2019-12-01'
                    ],
                    [
                        'type' => 'booking_days',
                        'minimum' => null,
                        'maximum' => 14
                    ]
                ]
            ]
        ];


        // no channel set so keep it - this is because it is only being parsed and for realtime stuff,
        // the pricing context channel will handle this anyway
        $structureOptions = new StructureOptions();

        $sdParser = new ModifiersParser();

        $modifiers = $sdParser->parse($specialDiscounts, $structureOptions);

        $this->assertCount(2, $modifiers);
        foreach($modifiers as $modifier) {
            $this->assertTrue($modifier->satisfiesSpecialDiscountCriteria());
        }
    }

    public function testTransformSpecialDiscounts(): void
    {
        $discounts = json_decode(file_get_contents(__DIR__ . '/Resources/special-discounts.json'), true);

        $structureOptions = new StructureOptions();

        $sdParser = new ModifiersParser();

        $modifiers = $sdParser->parse($discounts, $structureOptions);

        $spt = new SpecialDiscountTransformer();

        $result = $spt->transformSpecialDiscounts($modifiers);

        $this->assertCount(1, $result);
    }

}