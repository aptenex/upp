<?php

namespace Tests;

use Aptenex\Upp\Util\ConfigUtils;
use PHPUnit\Framework\TestCase;
use Aptenex\Upp\Util\AvailabilityUtils;

class AvailabilityUtilsTest extends TestCase
{

    public function testExtractRanges(): void
    {
        $set = [
            'startDatum' => '2019-08-01',
            'sequence'   => 'YNNNNNNNNNNNNNNNNNNYYNNNNNNNNNNYYNNNNNYYYYYYYYYYYYYYNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN'
        ];

        /**
         * @var \DateTime $startDate
         * @var string[] $ranges
         */
        [$startDate, $ranges] = AvailabilityUtils::extractRanges($set);

        $expectedRanges = [
            'Y',
            'NNNNNNNNNNNNNNNNNN',
            'YY',
            'NNNNNNNNNN',
            'YY',
            'NNNNN',
            'YYYYYYYYYYYYYY',
            'NNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN',
            'YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY',
            'NNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN',
        ];

        $this->assertSame('2019-08-01', $startDate->format('Y-m-d'));
        $this->assertSame($expectedRanges, $ranges);
    }

    public function testGetAvailabilityRangesIncludingState(): void
    {
        $set = [
            'startDatum' => '2019-08-01',
            'sequence'   => 'YNNNNNNNNNNNNNNNNNNYYNNNNNNNNNNYYNNNNNYYYYYYYYYYYYYYNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNYYYYYYYYYYYYYYYNYNYNYNYNYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNY'
        ];

        $expectedRanges = '[{"startDate":"2019-08-01 10:00:00","endDate":"2019-08-01 14:00:00","type":"Y"},{"startDate":"2019-08-02 14:00:00","endDate":"2019-08-19 10:00:00","type":"N"},{"startDate":"2019-08-20 14:00:00","endDate":"2019-08-21 10:00:00","type":"Y"},{"startDate":"2019-08-22 14:00:00","endDate":"2019-08-31 10:00:00","type":"N"},{"startDate":"2019-09-01 14:00:00","endDate":"2019-09-02 10:00:00","type":"Y"},{"startDate":"2019-09-03 14:00:00","endDate":"2019-09-07 10:00:00","type":"N"},{"startDate":"2019-09-08 14:00:00","endDate":"2019-09-21 10:00:00","type":"Y"},{"startDate":"2019-09-22 14:00:00","endDate":"2019-10-28 10:00:00","type":"N"},{"startDate":"2019-10-29 14:00:00","endDate":"2019-11-12 10:00:00","type":"Y"},{"startDate":"2019-11-13 10:00:00","endDate":"2019-11-13 14:00:00","type":"N"},{"startDate":"2019-11-14 10:00:00","endDate":"2019-11-14 14:00:00","type":"Y"},{"startDate":"2019-11-15 10:00:00","endDate":"2019-11-15 14:00:00","type":"N"},{"startDate":"2019-11-16 10:00:00","endDate":"2019-11-16 14:00:00","type":"Y"},{"startDate":"2019-11-17 10:00:00","endDate":"2019-11-17 14:00:00","type":"N"},{"startDate":"2019-11-18 10:00:00","endDate":"2019-11-18 14:00:00","type":"Y"},{"startDate":"2019-11-19 10:00:00","endDate":"2019-11-19 14:00:00","type":"N"},{"startDate":"2019-11-20 10:00:00","endDate":"2019-11-20 14:00:00","type":"Y"},{"startDate":"2019-11-21 10:00:00","endDate":"2019-11-21 14:00:00","type":"N"},{"startDate":"2019-11-22 14:00:00","endDate":"2021-03-31 10:00:00","type":"Y"},{"startDate":"2021-04-01 14:00:00","endDate":"2022-07-30 10:00:00","type":"N"},{"startDate":"2022-07-31 10:00:00","endDate":"2022-07-31 14:00:00","type":"Y"}]';

        $result = AvailabilityUtils::getAvailabilityRangesIncludingState($set, true);

        $this->assertSame(json_encode(json_decode($expectedRanges, true), JSON_PRETTY_PRINT), json_encode($result, JSON_PRETTY_PRINT));
    }

    public function testMergeAvailabilityStrings()
    {
        $strings = [
            'YYYYYYYNNNNNYYYYYNNNNNYYYYYYYYY',
            'YYYNNNNNYYYNNNNYYNNNNNYYNNNYY',
            'YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYNNNNN',
            'YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYNNNNN',
            'YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY'
        ];

        $this->assertSame('YYYNNNNNNNNNNNNYYNNNNNYYNNNYYYYNNNNNNNYYY', AvailabilityUtils::mergeAvailabilityStrings($strings));
    }

    public function testConvertDateRangesToAvailabilityString()
    {
        $ranges = [
            ['start' => '2019-01-01', 'end' => '2019-01-07'],
            ['start' => '2019-01-15', 'end' => '2019-01-30'],
            ['start' => '2019-02-04', 'end' => '2019-02-08'],
        ];

        $string = AvailabilityUtils::convertDateRangesToAvailabilityString(new \DateTime('2019-01-01'), $ranges, 50);

        $this->assertSame('YYYYYYYNNNNNNNYYYYYYYYYYYYYYYYNNNNYYYYYNNNNNNNNNNNN', $string);
    }

    public function testMergePricingPeriodsWithAvailabilityString()
    {
        $schema = json_decode('{
          "$locale": "en",
          "$marketingReferenceId": "PL-3AF",
          "$providerListingId": "2a14a98b-52b0-4592-a2e4-b836b8d515bc",
          "$schema": "https://raw.githubusercontent.com/aptenex/listing-schema/master/src/listing.json",
          "name": "Sotogrande Marina 3 Br Duplex With Large Terrace",
          "pricing": {
            "dynamic": {
              "data": [
                {
                  "currency": "EUR",
                  "defaults": {
                    "balanceDaysBeforeArrival": 45,
                    "bookableType": "enquiry_with_price",
                    "damageDeposit": 0,
                    "damageDepositCalculationMethod": "fixed",
                    "damageDepositSplitMethod": "ON_BALANCE",
                    "daysRequiredInAdvanceForBooking": 2,
                    "depositSplitPercentage": 30,
                    "extraNightAlterationStrategyUseGlobalNights": false,
                    "maximumNights": "",
                    "minimumNights": 0,
                    "partialWeekAlterationStrategyUseGlobalNights": false,
                    "perPetPerNight": 0,
                    "perPetPerStay": 0,
                    "perPetSplitMethod": "ON_TOTAL"
                  },
                  "modifiers":[],
                    "periods": [
                        {
                            "bookableType": null,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "arrivalDays": [],
                                    "departureDays": [],
                                    "endDate": "2019-04-12",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2019-01-07",
                                    "type": "date"
                                }
                            ],
                            "description": "Winter/Spring 2019",
                            "id": 4337,
                            "minimumNights": "5",
                            "priority": 500,
                            "rate": {
                                "amount": 133,
                                "applicableTaxes": [],
                                "basePriceOnly": false,
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "damageDeposit": 0,
                                "strategy": null,
                                "taxable": false,
                                "type": "nightly"
                            }
                        },
                        {
                            "bookableType": null,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "arrivalDays": [],
                                    "departureDays": [],
                                    "endDate": "2019-04-28",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2019-04-13",
                                    "type": "date"
                                }
                            ],
                            "description": "Easter 2019",
                            "id": 4338,
                            "minimumNights": "7",
                            "priority": 500,
                            "rate": {
                                "amount": 153,
                                "applicableTaxes": [],
                                "basePriceOnly": false,
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "damageDeposit": 0,
                                "strategy": null,
                                "taxable": false,
                                "type": "nightly"
                            }
                        },
                        {
                            "bookableType": null,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "arrivalDays": [],
                                    "departureDays": [],
                                    "endDate": "2019-05-31",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2019-04-29",
                                    "type": "date"
                                }
                            ],
                            "description": "May 2019",
                            "id": 4339,
                            "minimumNights": "5",
                            "priority": 500,
                            "rate": {
                                "amount": 165,
                                "applicableTaxes": [],
                                "basePriceOnly": false,
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "damageDeposit": 0,
                                "strategy": null,
                                "taxable": false,
                                "type": "nightly"
                            }
                        },
                        {
                            "bookableType": null,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "arrivalDays": [],
                                    "departureDays": [],
                                    "endDate": "2019-06-30",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2019-06-01",
                                    "type": "date"
                                }
                            ],
                            "description": "June 2019",
                            "id": 4340,
                            "minimumNights": "5",
                            "priority": 500,
                            "rate": {
                                "amount": 185,
                                "applicableTaxes": [],
                                "basePriceOnly": false,
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "damageDeposit": 0,
                                "strategy": null,
                                "taxable": false,
                                "type": "nightly"
                            }
                        },
                        {
                            "bookableType": null,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "arrivalDays": [],
                                    "departureDays": [],
                                    "endDate": "2019-07-31",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2019-07-01",
                                    "type": "date"
                                }
                            ],
                            "description": "July 2019",
                            "id": 4341,
                            "minimumNights": "7",
                            "priority": 500,
                            "rate": {
                                "amount": 220,
                                "applicableTaxes": [],
                                "basePriceOnly": false,
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "damageDeposit": 0,
                                "strategy": null,
                                "taxable": false,
                                "type": "nightly"
                            }
                        },
                        {
                            "bookableType": null,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "arrivalDays": [],
                                    "departureDays": [],
                                    "endDate": "2019-08-31",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2019-08-01",
                                    "type": "date"
                                }
                            ],
                            "description": "August 2019 ",
                            "id": 4342,
                            "minimumNights": "7",
                            "priority": 500,
                            "rate": {
                                "amount": 220,
                                "applicableTaxes": [],
                                "basePriceOnly": false,
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "damageDeposit": 0,
                                "strategy": null,
                                "taxable": false,
                                "type": "nightly"
                            }
                        },
                        {
                            "bookableType": null,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "arrivalDays": [],
                                    "departureDays": [],
                                    "endDate": "2019-09-30",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2019-09-01",
                                    "type": "date"
                                }
                            ],
                            "description": "September 2019",
                            "id": 4343,
                            "minimumNights": "5",
                            "priority": 500,
                            "rate": {
                                "amount": 185,
                                "applicableTaxes": [],
                                "basePriceOnly": false,
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "damageDeposit": 0,
                                "strategy": null,
                                "taxable": false,
                                "type": "nightly"
                            }
                        },
                        {
                            "bookableType": null,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "arrivalDays": [],
                                    "departureDays": [],
                                    "endDate": "2019-10-31",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2019-10-01",
                                    "type": "date"
                                }
                            ],
                            "description": "October 2019",
                            "id": 4344,
                            "minimumNights": "5",
                            "priority": 500,
                            "rate": {
                                "amount": 175,
                                "applicableTaxes": [],
                                "basePriceOnly": false,
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "damageDeposit": 0,
                                "strategy": null,
                                "taxable": false,
                                "type": "nightly"
                            }
                        },
                        {
                            "bookableType": null,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "arrivalDays": [],
                                    "departureDays": [],
                                    "endDate": "2019-12-20",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2019-11-01",
                                    "type": "date"
                                }
                            ],
                            "description": "Winter 2019 Pre Christmas",
                            "id": 4345,
                            "minimumNights": "5",
                            "priority": 500,
                            "rate": {
                                "amount": 170,
                                "applicableTaxes": [],
                                "basePriceOnly": false,
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "damageDeposit": 0,
                                "strategy": null,
                                "taxable": false,
                                "type": "nightly"
                            }
                        },
                        {
                            "bookableType": null,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "arrivalDays": [],
                                    "departureDays": [],
                                    "endDate": "2020-01-07",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2019-12-21",
                                    "type": "date"
                                }
                            ],
                            "description": "Christmas/New Year 2019/20",
                            "id": 4346,
                            "minimumNights": "5",
                            "priority": 500,
                            "rate": {
                                "amount": 180,
                                "applicableTaxes": [],
                                "basePriceOnly": false,
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "damageDeposit": 0,
                                "strategy": null,
                                "taxable": false,
                                "type": "nightly"
                            }
                        },
                        {
                            "bookableType": null,
                            "conditionOperand": "AND",
                            "conditions": [
                                {
                                    "arrivalDays": [],
                                    "departureDays": [],
                                    "endDate": "2020-04-04",
                                    "inverse": false,
                                    "modifyRatePerUnit": false,
                                    "startDate": "2020-01-08",
                                    "type": "date"
                                }
                            ],
                            "description": "Winter/Spring 2020",
                            "id": 4347,
                            "minimumNights": "5",
                            "priority": 500,
                            "rate": {
                                "amount": 165,
                                "applicableTaxes": [],
                                "basePriceOnly": false,
                                "calculationMethod": "fixed",
                                "calculationOperand": "equals",
                                "damageDeposit": 0,
                                "strategy": null,
                                "taxable": false,
                                "type": "nightly"
                            }
                        }
                    ],
                  "taxes": []
                }
              ],
              "meta": [],
              "name": "Pricing",
              "schema": "property-pricing",
              "version": "0.0.1"
            }
          },
          "unitAvailability": {
            "availabilityDefault": "N",
            "changeoverDefault": "3",
            "configuration": {
              "availability": "YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYNNYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY",
              "changeover": "3333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333",
              "maxStay": "30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30",
              "minStay": "5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0"
            },
            "dateRange": {
              "endDate": "2022-09-02",
              "startDate": "2019-09-03"
            },
            "instantBookableDefault": "N",
            "maxStayDefault": 30,
            "minPriorNotifyDefault": 2,
            "minStayDefault": 0
          }
        }', true);


            $availability = $schema['unitAvailability']['configuration']['availability'];
            $start = new \DateTime($schema['unitAvailability']['dateRange']['startDate']);

            $periodRanges = [];
            $cc = $schema['pricing']['dynamic']['data'][0];
            $expandedPeriods = ConfigUtils::expandPeriods($cc['periods']);

            foreach ($expandedPeriods as $period) {
                $startDate = $period['conditions'][0]['startDate'] ?? null;
                $endDate = $period['conditions'][0]['endDate'] ?? null;

                if ($startDate === null || $endDate === null) {
                    continue;
                }

                $periodRanges[] = [
                    'start' => new \DateTime($startDate),
                    'end'   => new \DateTime($endDate)
                ];
            }

            $pricingBlocks = AvailabilityUtils::convertDateRangesToAvailabilityString($start, $periodRanges, \strlen($availability));

            $newAvailability = AvailabilityUtils::mergeAvailabilityStrings([$pricingBlocks, $availability]);


            $ranges = AvailabilityUtils::getAvailabilityRangesIncludingState(
                [
                    'startDatum' => $schema['unitAvailability']['dateRange']['startDate'],
                    'sequence' => $newAvailability,
                ]
            );

            $expected = '[{"startDate":{"date":"2019-09-03 14:00:00.000000","timezone_type":3,"timezone":"UTC"},"endDate":{"date":"2020-04-04 10:00:00.000000","timezone_type":3,"timezone":"UTC"},"type":"Y"},{"startDate":{"date":"2020-04-05 14:00:00.000000","timezone_type":3,"timezone":"UTC"},"endDate":{"date":"2022-09-03 10:00:00.000000","timezone_type":3,"timezone":"UTC"},"type":"N"}]';
            $this->assertSame($expected, json_encode($ranges));

    }

}