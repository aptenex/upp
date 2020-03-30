<?php

namespace Tests;

use Aptenex\Upp\Builder\PricingConfigBuilder;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Parser\Resolver\HashMapPricingResolver;
use Aptenex\Upp\Parser\Structure\StructureOptions;
use Aptenex\Upp\Upp;
use Aptenex\Upp\Util\MoneyUtils;
use Money\Money;
use PHPUnit\Framework\TestCase;
use Aptenex\Upp\Translation\TestTranslator;

class ModifierCalculationSourceFeatureTest extends TestCase
{

    private $config = '{
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
            "maximumNights": "",
            "balanceDaysBeforeArrival": 0,
            "depositSplitPercentage": 0,
            "daysRequiredInAdvanceForBooking": 0,
            "bookableType": "instant_bookable",
            "extraNightAlterationStrategyUseGlobalNights": false,
            "damageDepositSplitMethod": "ON_TOTAL",
            "modifiersUseCategorizedCalculationOrder": true
          },
          "taxes": [],
          "periods": [
            {
              "description": "LOS high season 7 nights discount",
              "priority": 500,
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2020-01-01",
                  "endDate": "2020-09-30",
                  "arrivalDays": [],
                  "departureDays": []
                }
              ],
              "bookableType": null,
              "rate": {
                "type": "nightly",
                "amount": 100,
                "calculationMethod": "fixed",
                "calculationOperand": "equals",
                "applicableTaxes": [],
                "strategy": {
                  "extraNightsAlteration": {
                    "applyToTotal": true,
                    "makePreviousNightsSameRate": true,
                    "calculationMethod": "percentage",
                    "calculationOperand": "equals",
                    "brackets": [
                      {
                        "night": "7+",
                        "amount": 0.9,
                        "days": [],
                        "matchAmount": 0,
                        "damageDepositOverride": null
                      }
                    ]
                  }
                }
              }
            }
          ],
          "modifiers": [
            {
              "type": "card_fee",
              "hidden": true,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "HIDDEN FEE ( TO COVER CARD FEES)",
              "conditionOperand": "AND",
              "conditions": [],
              "rate": {
                "type": "adjustment",
                "amount": 0.01,
                "calculationMethod": "percentage",
                "calculationOperand": "addition",
                "applicableTaxes": []
              }
            },
            {
              "type": "service_charge",
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "SERVICE FEE : 5% ",
              "conditionOperand": "AND",
              "conditions": [],
              "rate": {
                "type": "adjustment",
                "amount": 0.05,
                "calculationMethod": "percentage",
                "calculationOperand": "addition",
                "applicableTaxes": []
              }
            },
            {
              "type": "host_fee",
              "hidden": true,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "HOST FEE : 10% ",
              "conditionOperand": "AND",
              "conditions": [],
              "rate": {
                "type": "adjustment",
                "amount": 0.1,
                "calculationMethod": "percentage",
                "calculationOperand": "addition",
                "applicableTaxes": []
              }
            },
            {
              "type": "modifier",
              "hidden": true,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "MOD WE5",
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "weekdays",
                  "modifyRatePerUnit": true,
                  "weekdays": [
                    "friday",
                    "saturday"
                  ]
                }
              ],
              "rate": {
                "type": "adjustment",
                "amount": 5,
                "calculationMethod": "fixed",
                "calculationOperand": "addition",
                "applicableTaxes": []
              }
            },
            {
              "type": "discount",
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "MOD EB6 10%",
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "booking_days",
                  "modifyRatePerUnit": false,
                  "minimum": 180
                }
              ],
              "rate": {
                "type": "adjustment",
                "amount": 0.1,
                "calculationMethod": "percentage",
                "calculationOperand": "subtraction",
                "applicableTaxes": []
              }
            },
            {
              "type": "discount",
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "MOD EB3 10%",
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "booking_days",
                  "modifyRatePerUnit": false,
                  "minimum": 90,
                  "maximum": 179
                }
              ],
              "rate": {
                "type": "adjustment",
                "amount": 0.1,
                "calculationMethod": "percentage",
                "calculationOperand": "subtraction",
                "applicableTaxes": []
              }
            },
            {
              "type": "discount",
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "MOD LM30 10%",
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "booking_days",
                  "modifyRatePerUnit": false,
                  "minimum": 16,
                  "maximum": 30
                }
              ],
              "rate": {
                "type": "adjustment",
                "amount": 0.1,
                "calculationMethod": "percentage",
                "calculationOperand": "subtraction",
                "applicableTaxes": []
              }
            },
            {
              "type": "discount",
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "MOD LM15 10%",
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "booking_days",
                  "modifyRatePerUnit": false,
                  "maximum": 15
                }
              ],
              "rate": {
                "type": "adjustment",
                "amount": 0.1,
                "calculationMethod": "percentage",
                "calculationOperand": "subtraction",
                "applicableTaxes": []
              }
            },
            {
              "type": "extra_guest_fee",
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "extra guest (incl 10% host fee)",
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "guests",
                  "modifyRatePerUnit": true,
                  "minimum": 3,
                  "maximum": 5
                },
                {
                  "type": "nights",
                  "modifyRatePerUnit": true
                }
              ],
              "rate": {
                "type": "adjustment",
                "amount": 11,
                "calculationMethod": "fixed",
                "calculationOperand": "addition",
                "applicableTaxes": []
              }
            },
            {
              "type": "cleaning",
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "Cleaning fee",
              "conditionOperand": "AND",
              "conditions": [],
              "rate": {
                "type": "adjustment",
                "amount": 25,
                "calculationMethod": "fixed",
                "calculationOperand": "addition",
                "applicableTaxes": []
              }
            }
          ]
        }
      ]
    }';

    private $config2 = '{
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
            "maximumNights": "",
            "balanceDaysBeforeArrival": 0,
            "depositSplitPercentage": 0,
            "daysRequiredInAdvanceForBooking": 0,
            "bookableType": "instant_bookable",
            "extraNightAlterationStrategyUseGlobalNights": false,
            "modifiersUseCategorizedCalculationOrder": true,
            "damageDepositSplitMethod": "ON_TOTAL"
          },
          "taxes": [],
          "periods": [
            {
              "description": "LOS high season 7 nights discount",
              "priority": 500,
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2020-01-01",
                  "endDate": "2020-09-30",
                  "arrivalDays": [],
                  "departureDays": []
                }
              ],
              "bookableType": null,
              "rate": {
                "type": "nightly",
                "amount": 100,
                "calculationMethod": "fixed",
                "calculationOperand": "equals",
                "applicableTaxes": [],
                "strategy": {
                  "extraNightsAlteration": {
                    "applyToTotal": true,
                    "makePreviousNightsSameRate": true,
                    "calculationMethod": "percentage",
                    "calculationOperand": "equals",
                    "brackets": [
                      {
                        "night": "7+",
                        "amount": 0.9,
                        "days": [],
                        "matchAmount": 0,
                        "damageDepositOverride": null
                      }
                    ]
                  }
                }
              }
            }
          ],
          "modifiers": [
            {
              "type": "card_fee",
              "hidden": true,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "HIDDEN FEE (TO COVER CARD FEES)",
              "conditionOperand": "AND",
              "conditions": [],
              "rate": {
                "type": "adjustment",
                "amount": 0.01,
                "calculationMethod": "percentage",
                "calculationOperand": "addition",
                "applicableTaxes": []
              }
            },
            {
              "type": "service_charge",
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "SERVICE FEE: 5%",
              "conditionOperand": "AND",
              "conditions": [],
              "rate": {
                "type": "adjustment",
                "amount": 0.05,
                "calculationMethod": "percentage",
                "calculationOperand": "addition",
                "applicableTaxes": []
              }
            },
            {
              "type": "host_fee",
              "hidden": true,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "HOST FEE: 10%",
              "conditionOperand": "AND",
              "conditions": [],
              "rate": {
                "type": "adjustment",
                "amount": 0.1,
                "calculationMethod": "percentage",
                "calculationOperand": "addition",
                "applicableTaxes": []
              }
            },
            {
              "type": "modifier",
              "hidden": true,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "MOD WE5",
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "weekdays",
                  "modifyRatePerUnit": true,
                  "weekdays": [
                    "friday",
                    "saturday"
                  ]
                }
              ],
              "rate": {
                "type": "adjustment",
                "amount": 5,
                "calculationMethod": "fixed",
                "calculationOperand": "addition",
                "applicableTaxes": []
              }
            },
            {
              "type": "discount",
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "MOD EB6 10%",
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "booking_days",
                  "modifyRatePerUnit": false,
                  "minimum": 180
                }
              ],
              "rate": {
                "type": "adjustment",
                "amount": 0.1,
                "calculationMethod": "percentage",
                "calculationOperand": "subtraction",
                "applicableTaxes": []
              }
            },
            {
              "type": "discount",
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "MOD EB3 10%",
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "booking_days",
                  "modifyRatePerUnit": false,
                  "minimum": 90,
                  "maximum": 179
                }
              ],
              "rate": {
                "type": "adjustment",
                "amount": 0.1,
                "calculationMethod": "percentage",
                "calculationOperand": "subtraction",
                "applicableTaxes": []
              }
            },
            {
              "type": "discount",
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "MOD LM30 10%",
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "booking_days",
                  "modifyRatePerUnit": false,
                  "minimum": 16,
                  "maximum": 30
                }
              ],
              "rate": {
                "type": "adjustment",
                "amount": 0.1,
                "calculationMethod": "percentage",
                "calculationOperand": "subtraction",
                "applicableTaxes": []
              }
            },
            {
              "type": "discount",
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "MOD LM15 10%",
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "booking_days",
                  "modifyRatePerUnit": false,
                  "maximum": 15
                }
              ],
              "rate": {
                "type": "adjustment",
                "amount": 0.1,
                "calculationMethod": "percentage",
                "calculationOperand": "subtraction",
                "applicableTaxes": []
              }
            },
            {
              "type": "extra_guest_fee",
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "extra guest (incl 10% host fee)",
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "guests",
                  "modifyRatePerUnit": true,
                  "minimum": 3,
                  "maximum": 5
                },
                {
                  "type": "nights",
                  "modifyRatePerUnit": true
                }
              ],
              "rate": {
                "type": "adjustment",
                "amount": 11,
                "calculationMethod": "fixed",
                "calculationOperand": "addition",
                "applicableTaxes": []
              }
            },
            {
              "type": "cleaning",
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "Cleaning fee",
              "conditionOperand": "AND",
              "conditions": [],
              "rate": {
                "type": "adjustment",
                "amount": 25,
                "calculationMethod": "fixed",
                "calculationOperand": "addition",
                "applicableTaxes": []
              }
            }
          ]
        }
      ]
    }';

    public function testModifierCalculationSourceFeatureWorksAsIntended()
    {
        $upp = new Upp(
            new HashMapPricingResolver(),
            new TestTranslator()
        );

        $options = new StructureOptions();

        $config = $upp->parsePricingConfig(json_decode($this->config, true), $options);

        $cc = $config->getCurrencyConfig('GBP');

        $this->assertTrue($cc->getDefaults()->isModifiersUseCategorizedCalculationOrder());

        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2020-03-05');
        $context->setArrivalDate('2020-06-01');
        $context->setDepartureDate('2020-06-02');
        $context->setGuests(2);

        $price = $upp->generatePrice($context, $config);

        $this->assertSame(100.00, MoneyUtils::getConvertedAmount(array_values($price->getStay()->getNights())[0]->getCost()));
        $this->assertSame(111.41, MoneyUtils::getConvertedAmount($price->getBasePrice()));
        $this->assertSame(141.91, MoneyUtils::getConvertedAmount($price->getTotal()));

        $this->assertSame(10.0, MoneyUtils::getConvertedAmount($price->getAdjustments()[0]->getAmount()));
        $this->assertSame(5.5, MoneyUtils::getConvertedAmount($price->getAdjustments()[1]->getAmount()));
        $this->assertSame(25.0, MoneyUtils::getConvertedAmount($price->getAdjustments()[2]->getAmount()));
        $this->assertSame(1.41, MoneyUtils::getConvertedAmount($price->getAdjustments()[3]->getAmount()));

        $context2 = new PricingContext();
        $context2->setCurrency('GBP');
        $context2->setBookingDate('2020-03-10');
        $context2->setArrivalDate('2020-03-14');
        $context2->setDepartureDate('2020-03-15');
        $context2->setGuests(2);

        $price2 = $upp->generatePrice($context2, $config);

        $this->assertSame(136.08, MoneyUtils::getConvertedAmount($price2->getTotal()));

        $lastAdjustment = \count($price2->getAdjustments()) - 1;
        $lastAdjustmentAmount = $price2->getAdjustments()[$lastAdjustment]->getAmount();

        $this->assertSame(1.35, MoneyUtils::getConvertedAmount($lastAdjustmentAmount));
    }

    /**
     * @throws \Aptenex\Upp\Exception\InvalidPricingConfigException
     */
    public function testLastMinuteAndEarlyBirdDiscountsApplyCorrectly()
    {
        $upp = new Upp(
            new HashMapPricingResolver(),
            new TestTranslator()
        );

        $options = new StructureOptions();

        $config = $upp->parsePricingConfig(json_decode($this->config2, true), $options);

        $cc = $config->getCurrencyConfig('GBP');

        $this->assertTrue($cc->getDefaults()->isModifiersUseCategorizedCalculationOrder());

        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2020-03-26');
        $context->setArrivalDate('2020-03-30');
        $context->setDepartureDate('2020-04-02');
        $context->setGuests(2);

        $price = $upp->generatePrice($context, $config);

        $this->assertSame(30.0, MoneyUtils::getConvertedAmount($price->getAdjustmentByDescription('MOD LM15 10%')->getAmount()));
        $this->assertSame(27.0, MoneyUtils::getConvertedAmount($price->getAdjustmentByDescription('HOST FEE: 10%')->getAmount()));
        $this->assertSame(14.85, MoneyUtils::getConvertedAmount($price->getAdjustmentByDescription('SERVICE FEE: 5%')->getAmount()));
        $this->assertSame(25.0, MoneyUtils::getConvertedAmount($price->getAdjustmentByDescription('Cleaning fee')->getAmount()));
        $this->assertSame(3.37, MoneyUtils::getConvertedAmount($price->getAdjustmentByDescription('HIDDEN FEE (TO COVER CARD FEES)')->getAmount()));

        $this->assertSame(300.37, MoneyUtils::getConvertedAmount($price->getBasePrice()));
        $this->assertSame(340.22, MoneyUtils::getConvertedAmount($price->getTotal()));
    }

}