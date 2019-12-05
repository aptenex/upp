<?php

namespace Tests;

use Aptenex\Upp\Parser\SpecialDiscountsParser;
use Aptenex\Upp\Parser\Structure\Modifier;
use Aptenex\Upp\Upp;
use Aptenex\Upp\Util\ConfigUtils;
use Aptenex\Upp\Util\TestUtils;
use Translation\TestTranslator;
use PHPUnit\Framework\TestCase;
use Aptenex\Upp\Exception\InvalidPricingConfigException;
use Aptenex\Upp\Parser\Resolver\HashMapPricingResolver;
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

        $sdParser = new SpecialDiscountsParser();

        $modifiers = $sdParser->parse($specialDiscounts, $structureOptions);

        $this->assertCount(2, $modifiers);
    }

}