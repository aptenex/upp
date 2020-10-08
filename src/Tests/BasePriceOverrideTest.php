<?php

namespace Tests;

use Aptenex\Upp\Upp;
use Aptenex\Upp\Util\MoneyUtils;
use PHPUnit\Framework\TestCase;
use Aptenex\Upp\Translation\TestTranslator;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Parser\Structure\Defaults;
use Aptenex\Upp\Parser\Structure\StructureOptions;
use Aptenex\Upp\Parser\Resolver\HashMapPricingResolver;
use Aptenex\Upp\Parser\ExternalConfig\ExternalCommandDirector;
use Aptenex\Upp\Parser\ExternalConfig\BasePriceOverrideCommand;

class BasePriceOverrideTest extends TestCase
{

    private static $json1 = '{
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
            "minimumNights": 3,
            "bookableType": null,
            "balanceDaysBeforeArrival": 0,
            "depositSplitPercentage": 30,
            "extraNightAlterationStrategyUseGlobalNights": false,
            "damageDepositSplitMethod": "ON_DEPOSIT"
          },
          "taxes": [],
          "periods": [
            {
              "description": "Standard",
              "priority": 500,
              "conditionOperand": "AND",
              "bookableType": null,
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2017-06-24",
                  "endDate": "2020-06-30",
                  "arrivalDays": [],
                  "departureDays": []
                }
              ],
              "rate": {
                "type": "nightly",
                "amount": 250,
                "calculationMethod": "fixed",
                "calculationOperand": "equals"
              }
            }
          ],
          "modifiers": []
        }
      ]
    }';

    public function testSuccessfulOverride()
    {
        $upp = new Upp(
            new HashMapPricingResolver(),
            new TestTranslator()
        );

        $defaults = new Defaults();
        $defaults->setDaysRequiredInAdvanceForBooking(1);

        $options = new StructureOptions();
        $options->setExternalCommandDirector(new ExternalCommandDirector([
            new BasePriceOverrideCommand(1227.45)
        ]));

        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2017-05-01');
        $context->setArrivalDate('2017-06-26');
        $context->setDepartureDate('2017-06-30');
        $context->setGuests(3);

        $config = $upp->parsePricingConfig(json_decode(self::$json1, true), $options);

        $pricing = $upp->generatePrice($context, $config);

        $this->assertInstanceOf(FinalPrice::class, $pricing);
        $this->assertSame(1227.45, MoneyUtils::getConvertedAmount($pricing->getBasePrice()));

    }

    public function testVillaSumaVatCalculationErrorRegression()
    {
        $upp = new Upp(
            new HashMapPricingResolver(),
            new TestTranslator()
        );

        $config = '
            {
                "name": "Pricing",
                "schema": "property-pricing",
                "version": "0.0.1",
                "meta": [],
                "data": [
                    {
                        "currency": "USD",
                        "defaults": {
                            "damageDeposit": 0,
                            "damageDepositCalculationMethod": "fixed",
                            "perPetPerStay": 0,
                            "perPetPerNight": 0,
                            "perPetSplitMethod": "ON_TOTAL",
                            "minimumNights": 0,
                            "maximumNights": "",
                            "balanceDaysBeforeArrival": 30,
                            "depositSplitPercentage": 30,
                            "daysRequiredInAdvanceForBooking": 0,
                            "bookableType": "instant_bookable",
                            "periodSelectionStrategy": "DEFAULT",
                            "extraNightAlterationStrategyUseGlobalNights": false,
                            "modifiersUseCategorizedCalculationOrder": true,
                            "damageDepositSplitMethod": "ON_DEPOSIT"
                        },
                        "taxes": [
                            {
                                "name": "VAT",
                                "uuid": "1d4213a8-ee3f-499d-8fef-1d4763668fe4",
                                "type": "TYPE_VAT",
                                "amount": 0.07,
                                "includeBasePrice": true,
                                "calculationMethod": "percentage"
                            }
                        ],
                        "periods": [],
                        "modifiers": [
                            {
                                "type": "service_charge",
                                "hidden": false,
                                "splitMethod": "ON_TOTAL",
                                "priceGroup": "total",
                                "description": "Service charge",
                                "conditionOperand": "AND",
                                "conditions": [],
                                "rate": {
                                    "type": "adjustment",
                                    "amount": 0.1,
                                    "calculationMethod": "percentage",
                                    "calculationOperand": "addition",
                                    "taxable": true,
                                    "applicableTaxes": [
                                        "1d4213a8-ee3f-499d-8fef-1d4763668fe4"
                                    ],
                                    "daysOfWeek": null
                                }
                            },
                            {
                                "type": "discount",
                                "hidden": false,
                                "splitMethod": "ON_TOTAL",
                                "priceGroup": "total",
                                "description": "Early bird 10%",
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
                                    "applicableTaxes": [],
                                    "daysOfWeek": null
                                }
                            },
                            {
                                "type": "discount",
                                "hidden": false,
                                "splitMethod": "ON_TOTAL",
                                "priceGroup": "total",
                                "description": "Last minute 15%",
                                "conditionOperand": "AND",
                                "conditions": [
                                    {
                                        "type": "booking_days",
                                        "modifyRatePerUnit": false,
                                        "maximum": 30
                                    }
                                ],
                                "rate": {
                                    "type": "adjustment",
                                    "amount": 0.15,
                                    "calculationMethod": "percentage",
                                    "calculationOperand": "subtraction",
                                    "applicableTaxes": [],
                                    "daysOfWeek": null
                                }
                            }
                        ]
                    },
                    {
                        "currency": "THB",
                        "defaults": {
                            "damageDeposit": 0,
                            "damageDepositCalculationMethod": "fixed",
                            "perPetPerStay": 0,
                            "perPetPerNight": 0,
                            "perPetSplitMethod": "ON_TOTAL",
                            "minimumNights": 0,
                            "maximumNights": "",
                            "balanceDaysBeforeArrival": 30,
                            "depositSplitPercentage": 30,
                            "daysRequiredInAdvanceForBooking": 0,
                            "bookableType": "instant_bookable",
                            "periodSelectionStrategy": "DEFAULT",
                            "extraNightAlterationStrategyUseGlobalNights": false,
                            "modifiersUseCategorizedCalculationOrder": true,
                            "damageDepositSplitMethod": "ON_DEPOSIT"
                        },
                        "taxes": [
                            {
                                "name": "VAT",
                                "uuid": "4bcdfa5b-e635-47d9-9969-39f7405fbca8",
                                "type": "TYPE_VAT",
                                "amount": 0.07,
                                "includeBasePrice": true,
                                "calculationMethod": "percentage",
                                "longStayExemption": ""
                            }
                        ],
                        "periods": [],
                        "modifiers": [
                            {
                                "type": "modifier",
                                "hidden": false,
                                "splitMethod": "ON_TOTAL",
                                "priceGroup": "total",
                                "description": "Service charge",
                                "conditionOperand": "AND",
                                "conditions": [],
                                "rate": {
                                    "type": "adjustment",
                                    "amount": 0.1,
                                    "calculationMethod": "percentage",
                                    "calculationOperand": "addition",
                                    "applicableTaxes": [],
                                    "daysOfWeek": null
                                }
                            }
                        ]
                    }
                ]
            }
        ';

        $defaults = new Defaults();
        $defaults->setDaysRequiredInAdvanceForBooking(1);

        $options = new StructureOptions();
        $options->setExternalCommandDirector(new ExternalCommandDirector([
            new BasePriceOverrideCommand(44010.2)
        ]));

        $context = new PricingContext();
        $context->setCurrency('THB');
        $context->setBookingDate('2020-10-05');
        $context->setArrivalDate('2020-11-19');
        $context->setDepartureDate('2020-11-21');
        $context->setGuests(13);

        $config = $upp->parsePricingConfig(json_decode($config, true), $options);

        $pricing = $upp->generatePrice($context, $config);

        $this->assertInstanceOf(FinalPrice::class, $pricing);
        $this->assertSame('4401020', $pricing->getBasePrice()->getAmount());

        foreach($pricing->getAdjustments() as $adjustment) {
            if ($adjustment->getDescription() === 'Service Charge') {
                $this->assertSame('440102', $adjustment->getAmount()->getAmount());
            } else if ($adjustment->getDescription() === 'VAT') {
                $this->assertSame('308071', $adjustment->getAmount()->getAmount());
            }
        }
    }

}