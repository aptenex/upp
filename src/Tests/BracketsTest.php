<?php

namespace Tests;

use Aptenex\Upp\Calculation\Pricing\Strategy\BracketsEvaluator;
use PHPUnit\Framework\TestCase;

class BracketsTest extends TestCase
{

    public function testNormalExpandBrackets()
    {
        $be = new BracketsEvaluator();

        $brackets = [
            [
                'night'  => '4-8',
                'guests' => null,
                'amount' => 70
            ],
            [
                'night'  => '9+',
                'guests' => null,
                'amount' => 100
            ],
            [
                'night'  => '1-3',
                'guests' => null,
                'amount' => 50
            ]
        ];

        $expected = '{"1":50,"2":50,"3":50,"4":70,"5":70,"6":70,"7":70,"8":70,"9":100,"10":100,"11":100,"12":100,"13":100}';

        $this->assertSame($expected, json_encode($be->expandBrackets($brackets, 13)));
    }

    public function testGuestExpandBrackets()
    {
        $be = new BracketsEvaluator();

        $brackets = [
            [
                'night'  => '1-3',
                'guests' => [
                    [
                        'guests' => '2',
                        'amount' => 60,
                    ],
                    [
                        'guests' => '3+',
                        'amount' => 70,
                    ]
                ],
                'amount' => 50
            ],
            [
                'night'  => '4-8',
                'guests' => [
                    [
                        'guests' => '2',
                        'amount' => 80,
                    ],
                    [
                        'guests' => '3+',
                        'amount' => 90,
                    ]
                ],
                'amount' => 70
            ],
            [
                'night'  => '9+',
                'guests' => null,
                'amount' => 100
            ],
        ];


        $expected = '{"1":{"2":60,"3":70,"4":70,"5":70,"6":70,"_default":50},"2":{"2":60,"3":70,"4":70,"5":70,"6":70,"_default":50},"3":{"2":60,"3":70,"4":70,"5":70,"6":70,"_default":50},"4":{"2":80,"3":90,"4":90,"5":90,"6":90,"_default":70},"5":{"2":80,"3":90,"4":90,"5":90,"6":90,"_default":70},"6":{"2":80,"3":90,"4":90,"5":90,"6":90,"_default":70},"7":{"2":80,"3":90,"4":90,"5":90,"6":90,"_default":70},"8":{"2":80,"3":90,"4":90,"5":90,"6":90,"_default":70},"9":{"_default":100},"10":{"_default":100},"11":{"_default":100},"12":{"_default":100},"13":{"_default":100}}';

        $this->assertSame($expected, json_encode($be->expandBracketsWithGuests($brackets, 13, 6)));
    }

}