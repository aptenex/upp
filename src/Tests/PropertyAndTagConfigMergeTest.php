<?php

namespace Tests;

use Aptenex\Upp\Builder\PricingConfigBuilder;
use Aptenex\Upp\Upp;
use PHPUnit\Framework\TestCase;
use Translation\TestTranslator;
use Aptenex\Upp\Parser\Structure\StructureOptions;
use Aptenex\Upp\Parser\Resolver\HashMapPricingResolver;

class PropertyAndTagConfigMergeTest extends TestCase
{

    private $propertyJson = '{
        "name": "Pricing",
        "schema": "property-pricing",
        "version": "0.0.1",
        "meta": [],
        "data": [
            {
                "currency": "GBP",
                "defaults": {
                    "damageDeposit": 0,
                    "damageDepositCalculationMethod": "fixed",
                    "perPetPerStay": 0,
                    "perPetPerNight": 0,
                    "perPetSplitMethod": "ON_TOTAL",
                    "minimumNights": 0,
                    "balanceDaysBeforeArrival": 0,
                    "depositSplitPercentage": 0,
                    "daysRequiredInAdvanceForBooking": 0,
                    "extraNightAlterationStrategyUseGlobalNights": false
                },
                "taxes": [
                    {
                        "name": "Test Tax",
                        "uuid": "677188f0-d2c2-45df-bdf0-35a0e1bd5388",
                        "type": "TYPE_TAX",
                        "amount": 0.05,
                        "calculationMethod": "percentage",
                        "includeBasePrice": true
                    }
                ],
                "periods": [
                    {
                        "description": "Example Period",
                        "priority": 500,
                        "conditionOperand": "AND",
                        "conditions": [
                            {
                                "type": "date",
                                "modifyRatePerUnit": false,
                                "startDate": "2019-02-01",
                                "endDate": "2021-02-01",
                                "arrivalDays": [],
                                "departureDays": []
                            }
                        ],
                        "minimumNights": "0",
                        "bookableType": null,
                        "rate": {
                            "type": "nightly",
                            "amount": 100,
                            "calculationMethod": "fixed",
                            "calculationOperand": "equals",
                            "applicableTaxes": []
                        }
                    }
                ],
                "modifiers": [
                    {
                        "type": "cleaning",
                        "hidden": false,
                        "splitMethod": "ON_TOTAL",
                        "description": "Cleaning",
                        "conditionOperand": "AND",
                        "conditions": [],
                        "rate": {
                            "type": "adjustment",
                            "amount": 100,
                            "calculationMethod": "fixed",
                            "calculationOperand": "addition",
                            "applicableTaxes": []
                        }
                    }
                ]
            }
        ]
    }';

    private $tagOne = '
        {
          "name": "Pricing",
          "schema": "tag-pricing",
          "version": "0.0.1",
          "meta": [],
          "data": [
            {
              "currency": "GBP",
              "defaults": {
                "damageDeposit": 0,
                "damageDepositCalculationMethod": "fixed",
                "perPetPerStay": 0,
                "perPetPerNight": 0,
                "perPetSplitMethod": "ON_TOTAL",
                "minimumNights": 0,
                "maximumNights": "",
                "balanceDaysBeforeArrival": 0,
                "depositSplitPercentage": 0,
                "daysRequiredInAdvanceForBooking": 0,
                "bookableType": "instant_bookable",
                "extraNightAlterationStrategyUseGlobalNights": false
              },
              "taxes": [
                {
                  "name": "VAT",
                  "uuid": "7b4caccf-e2b2-4dfa-a542-33a9d806b4ca",
                  "type": "TYPE_VAT",
                  "amount": 0.2,
                  "includeBasePrice": true,
                  "calculationMethod": "percentage"
                }
              ],
              "periods": [],
              "modifiers": [
                {
                  "type": "extra_guest_fee",
                  "hidden": true,
                  "splitMethod": "ON_TOTAL",
                  "priceGroup": "total",
                  "description": "Per Guest Per Night",
                  "conditionOperand": "AND",
                  "conditions": [
                    {
                      "type": "nights",
                      "modifyRatePerUnit": true
                    },
                    {
                      "type": "guests",
                      "modifyRatePerUnit": true
                    }
                  ],
                  "rate": {
                    "type": "adjustment",
                    "amount": 10,
                    "calculationMethod": "fixed",
                    "calculationOperand": "addition",
                    "applicableTaxes": []
                  }
                }
              ]
            }
          ]
        }
    ';

    private $tagTwo = '
        {
          "name": "Pricing",
          "schema": "tag-pricing",
          "version": "0.0.1",
          "meta": [],
          "data": [
            {
              "currency": "GBP",
              "defaults": {
                "damageDeposit": 0,
                "damageDepositCalculationMethod": "fixed",
                "perPetPerStay": 0,
                "perPetPerNight": 0,
                "perPetSplitMethod": "ON_TOTAL",
                "minimumNights": 0,
                "maximumNights": "",
                "balanceDaysBeforeArrival": 0,
                "depositSplitPercentage": 0,
                "daysRequiredInAdvanceForBooking": 0,
                "bookableType": "instant_bookable",
                "extraNightAlterationStrategyUseGlobalNights": false
              },
              "taxes": [
                {
                  "name": "VAT2",
                  "uuid": "7b4caccf-e2b2-4dfa-a542-33a9d806b4ca",
                  "type": "TYPE_VAT",
                  "amount": 0.2,
                  "includeBasePrice": true,
                  "calculationMethod": "percentage"
                }
              ],
              "periods": [],
              "modifiers": [
                {
                  "type": "extra_guest_fee",
                  "hidden": true,
                  "splitMethod": "ON_TOTAL",
                  "priceGroup": "total",
                  "description": "Per Guest Per Night 2",
                  "conditionOperand": "AND",
                  "conditions": [
                    {
                      "type": "nights",
                      "modifyRatePerUnit": true
                    },
                    {
                      "type": "guests",
                      "modifyRatePerUnit": true
                    }
                  ],
                  "rate": {
                    "type": "adjustment",
                    "amount": 10,
                    "calculationMethod": "fixed",
                    "calculationOperand": "addition",
                    "applicableTaxes": []
                  }
                }
              ]
            }
          ]
        }
    ';

    public function testPropertyAndTagMerge()
    {
        $upp = new Upp(
            new HashMapPricingResolver(),
            new TestTranslator()
        );

        $options = new StructureOptions();

        $mergedConfig = (new PricingConfigBuilder())->buildConfig(
            json_decode($this->propertyJson, true),
            [
                json_decode($this->tagOne, true),
                json_decode($this->tagTwo, true)
            ]
        );

        $config = $upp->parsePricingConfig($mergedConfig, $options);

        $this->assertCount(3, $config->getCurrencyConfig('GBP')->getTaxes());
        $this->assertCount(3, $config->getCurrencyConfig('GBP')->getModifiers());
    }

}