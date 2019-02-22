<?php

namespace Tests;

use Aptenex\Upp\Util\DateUtils;
use PHPUnit\Framework\TestCase;

class ExpandPeriodsTest extends TestCase
{

    public function testExpandPeriods()
    {

        $periods = [
            [
                'conditions' => [
                    [
                        'type' => 'date',
                        'startDate' => '2019-01-01',
                        'endDate' => '2019-12-31'
                    ]
                ]
            ],
            [
                'conditions' => [
                    [
                        'type' => 'date',
                        'startDate' => '2019-02-01',
                        'endDate' => '2019-02-28'
                    ]
                ]
            ],
            /*[
                'conditions' => [
                    [
                        'type' => 'date',
                        'startDate' => '2019-06-01',
                        'endDate' => '2019-07-20'
                    ]
                ]
            ],
            [
                'conditions' => [
                    [
                        'type' => 'date',
                        'startDate' => '2019-02-12',
                        'endDate' => '2019-02-16'
                    ]
                ]
            ]*/
        ];

        $expanded = DateUtils::expandPeriods($periods);

        dump($expanded);
        exit;

    }

}