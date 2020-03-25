<?php

namespace Aptenex\Upp\Tests;

use Aptenex\Upp\Upp;
use Aptenex\Upp\Translation\TestTranslator;
use PHPUnit\Framework\TestCase;
use Aptenex\Upp\Transformer\RentivoTransformer;
use Aptenex\Upp\Parser\Resolver\HashMapPricingResolver;
use Aptenex\Upp\Exception\InvalidPricingConfigException;

class RentivoTransformerTest extends TestCase
{

    public function testMonthlyTypeTransformation()
    {
        $jsonConfig1 = '{
          "name": "Pricing",
          "schema": "property-pricing",
          "version": "0.0.1",
          "meta": [],
          "data": [
            {
              "currency": "THB",
              "defaults": {
                "damageDeposit": 0,
                "damageDepositCalculationMethod": "fixed",
                "minimumNights": 30,
                "balanceDaysBeforeArrival": 42,
                "depositSplitPercentage": 0,
                "daysRequiredInAdvanceForBooking": 0,
                "extraNightAlterationStrategyUseGlobalNights": false,
                "damageDepositSplitMethod": "ON_BALANCE"
              },
              "taxes": [],
              "periods": [
                {
                  "description": "Long term rates 2018-2024",
                  "priority": 500,
                  "conditionOperand": "AND",
                  "conditions": [
                    {
                      "type": "date",
                      "modifyRatePerUnit": false,
                      "startDate": "2018-01-01",
                      "endDate": "2025-01-01",
                      "arrivalDays": [],
                      "departureDays": []
                    }
                  ],
                  "bookableType": null,
                  "rate": {
                    "type": "monthly",
                    "amount": 30000,
                    "calculationMethod": "fixed",
                    "calculationOperand": "equals",
                    "applicableTaxes": [],
                    "strategy": {
                      "extraMonthsAlteration": {
                        "applyToTotal": true,
                        "makePreviousMonthsSameRate": true,
                        "calculationMethod": "fixed",
                        "calculationOperand": "equals",
                        "extraNightsRate": 1200,
                        "brackets": [
                          {
                            "night": "1-3",
                            "amount": 36000,
                            "days": [],
                            "matchAmount": 0,
                            "damageDepositOverride": 36000
                          },
                          {
                            "night": "4-6",
                            "amount": 34500,
                            "days": [],
                            "matchAmount": 0,
                            "damageDepositOverride": 34500
                          },
                          {
                            "night": "7-9",
                            "amount": 33000,
                            "days": [],
                            "matchAmount": 0,
                            "damageDepositOverride": 66000
                          },
                          {
                            "night": "10+",
                            "amount": 30000,
                            "days": [],
                            "matchAmount": 0,
                            "damageDepositOverride": 60000
                          }
                        ]
                      }
                    }
                  }
                }
              ],
              "modifiers": []
            }
          ]
        }';

        $upp = new Upp(
            new HashMapPricingResolver(),
            new TestTranslator()
        );

        $config = $upp->parsePricingConfig(json_decode($jsonConfig1, true));

        try {
            $transformed = $upp->transformPricingConfig($config, new RentivoTransformer());

            $this->assertEquals(1000.00, $transformed['summary']['pricingLow']);
            $this->assertEquals(1000.00, $transformed['summary']['pricingHigh']);
        } catch (InvalidPricingConfigException $e) {
            $this->fail('Failed to transform: ' . $e->getMessage());
        }
    }

}