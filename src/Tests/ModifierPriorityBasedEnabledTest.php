<?php

namespace Tests;

use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Parser\Resolver\HashMapPricingResolver;
use Aptenex\Upp\Parser\Structure\StructureOptions;
use Aptenex\Upp\Upp;
use Aptenex\Upp\Util\MoneyUtils;
use PHPUnit\Framework\TestCase;
use Aptenex\Upp\Translation\TestTranslator;

class ModifierPriorityBasedEnabledTest extends TestCase
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
            "modifiersUseCategorizedCalculationOrder": true,
            "enablePriorityBasedModifiers": true
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
              "type": "discount",
              "priority": 3,
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "MOD EB6 10%",
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "booking_days",
                  "modifyRatePerUnit": false,
                  "minimum": 90
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
              "priority": 3,
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "MOD EB3 10%",
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "booking_days",
                  "modifyRatePerUnit": false,
                  "minimum": 90
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
              "priority": 1,
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "MOD LM30 10%",
              "conditionOperand": "AND",
              "conditions": [],
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
              "priority": 2,
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "MOD LM15 10%",
              "conditionOperand": "AND",
              "conditions": [],
              "rate": {
                "type": "adjustment",
                "amount": 0.1,
                "calculationMethod": "percentage",
                "calculationOperand": "subtraction",
                "applicableTaxes": []
              }
            }
          ]
        }
      ]
    }';

    public function testModifierPriorityEnabledCumulativeDiscounts()
    {
        $upp = new Upp(
            new HashMapPricingResolver(),
            new TestTranslator()
        );

        $options = new StructureOptions();

        $config = $upp->parsePricingConfig(json_decode($this->config, true), $options);

        $cc = $config->getCurrencyConfig('GBP');

        $this->assertTrue($cc->getDefaults()->isEnablePriorityBasedModifiers());

        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2020-03-05');
        $context->setArrivalDate('2020-06-01');
        $context->setDepartureDate('2020-06-02');
        $context->setGuests(2);

        $price = $upp->generatePrice($context, $config);

        $expectedAdjustments = ["MOD LM15 10%", "SERVICE FEE : 5%"];
        $actualAdjustments = [];
        foreach($price->getAdjustments() as $item) {
            $actualAdjustments[] = trim($item->getDescription());
        }

        $this->assertSame($expectedAdjustments, $actualAdjustments);

        $context2 = new PricingContext();
        $context2->setCurrency('GBP');
        $context2->setBookingDate('2019-01-10');
        $context2->setArrivalDate('2020-03-14');
        $context2->setDepartureDate('2020-03-15');
        $context2->setGuests(2);

        $price2 = $upp->generatePrice($context2, $config);

        $expectedAdjustments = ["MOD EB6 10%", "MOD EB3 10%", "SERVICE FEE : 5%"];
        $actualAdjustments = [];
        foreach($price2->getAdjustments() as $item) {
            $actualAdjustments[] = trim($item->getDescription());
        }

        $this->assertSame($expectedAdjustments, $actualAdjustments);
    }

}