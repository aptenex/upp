<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Aptenex\Upp\Util\ConfigUtils;

class ExpandPeriodsTest extends TestCase
{

    public function testNestedPeriodsExpansion(): void
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
            [
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
            ]
        ];

        $expanded = ConfigUtils::expandPeriods($periods);

        $this->assertCount(7, $expanded);

        $expected = '[{"conditions":[{"type":"date","startDate":"2019-01-01","endDate":"2019-01-31"}]},{"conditions":[{"type":"date","startDate":"2019-02-01","endDate":"2019-02-11"}]},{"conditions":[{"type":"date","startDate":"2019-02-12","endDate":"2019-02-16"}]},{"conditions":[{"type":"date","startDate":"2019-02-17","endDate":"2019-02-28"}]},{"conditions":[{"type":"date","startDate":"2019-03-01","endDate":"2019-05-31"}]},{"conditions":[{"type":"date","startDate":"2019-06-01","endDate":"2019-07-20"}]},{"conditions":[{"type":"date","startDate":"2019-07-21","endDate":"2019-12-31"}]}]';

        $this->assertSame($expected, json_encode($expanded));
    }

    public function testNonNestedRemainsTheSame(): void
    {

        $periods = [
            [
                'conditions' => [
                    [
                        'type' => 'date',
                        'startDate' => '2019-01-01',
                        'endDate' => '2019-01-31'
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
            [
                'conditions' => [
                    [
                        'type' => 'date',
                        'startDate' => '2019-06-01',
                        'endDate' => '2019-07-20'
                    ]
                ]
            ]
        ];

        $expanded = ConfigUtils::expandPeriods($periods);

        $this->assertSame(json_encode($periods), json_encode($expanded));
    }

}