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
use Translation\TestTranslator;

class TotalReverseCalculationTest extends TestCase
{

    private $config = '{
      "name": "Pricing",
      "schema": "property-pricing",
      "version": "0.0.1",
      "meta": [],
      "data": [
        {
          "currency": "USD",
          "defaults": {
            "damageDeposit": 1000,
            "damageDepositCalculationMethod": "fixed",
            "perPetPerStay": 0,
            "perPetPerNight": 0,
            "perPetSplitMethod": "ON_TOTAL",
            "minimumNights": 3,
            "maximumNights": "",
            "balanceDaysBeforeArrival": 30,
            "depositSplitPercentage": 30,
            "daysRequiredInAdvanceForBooking": 0,
            "bookableType": "enquiry_only",
            "extraNightAlterationStrategyUseGlobalNights": false,
            "damageDepositSplitMethod": "ON_DEPOSIT"
          },
          "taxes": [
            {
              "name": "VAT",
              "uuid": "659e1307-d795-4d90-b8ef-2e2a4365f8e3",
              "type": "TYPE_VAT",
              "amount": 0.07,
              "includeBasePrice": true,
              "calculationMethod": "percentage"
            }
          ],
          "periods": [
            {
              "description": "Low Season",
              "priority": 500,
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2019-10-08",
                  "endDate": "2019-12-19",
                  "arrivalDays": [],
                  "departureDays": []
                }
              ],
              "minimumNights": "3",
              "bookableType": null,
              "rate": {
                "type": "nightly",
                "amount": 1020,
                "damageDeposit": null,
                "calculationMethod": "fixed",
                "calculationOperand": "equals",
                "applicableTaxes": []
              }
            },
            {
              "description": "Christmas Season",
              "priority": 500,
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2019-12-20",
                  "endDate": "2020-01-10",
                  "arrivalDays": [],
                  "departureDays": []
                }
              ],
              "minimumNights": "7",
              "bookableType": null,
              "rate": {
                "type": "nightly",
                "amount": 2240,
                "calculationMethod": "fixed",
                "calculationOperand": "equals",
                "applicableTaxes": []
              }
            },
            {
              "description": "High Season",
              "priority": 500,
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2020-01-11",
                  "endDate": "2020-01-22",
                  "arrivalDays": [],
                  "departureDays": []
                }
              ],
              "minimumNights": "3",
              "bookableType": null,
              "rate": {
                "type": "nightly",
                "amount": 1170,
                "calculationMethod": "fixed",
                "calculationOperand": "equals",
                "applicableTaxes": []
              }
            },
            {
              "description": "Chinese New year",
              "priority": 500,
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2020-01-23",
                  "endDate": "2020-02-01",
                  "arrivalDays": [],
                  "departureDays": []
                }
              ],
              "minimumNights": "7",
              "bookableType": null,
              "rate": {
                "type": "nightly",
                "amount": 2240,
                "calculationMethod": "fixed",
                "calculationOperand": "equals",
                "applicableTaxes": []
              }
            },
            {
              "description": "High Season",
              "priority": 500,
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2020-02-02",
                  "endDate": "2020-03-27",
                  "arrivalDays": [],
                  "departureDays": []
                }
              ],
              "minimumNights": "3",
              "bookableType": null,
              "rate": {
                "type": "nightly",
                "amount": 1170,
                "calculationMethod": "fixed",
                "calculationOperand": "equals",
                "applicableTaxes": []
              }
            },
            {
              "description": "Easter Season",
              "priority": 500,
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2020-03-28",
                  "endDate": "2020-04-25",
                  "arrivalDays": [],
                  "departureDays": []
                }
              ],
              "minimumNights": "7",
              "bookableType": null,
              "rate": {
                "type": "nightly",
                "amount": 1495,
                "calculationMethod": "fixed",
                "calculationOperand": "equals",
                "applicableTaxes": []
              }
            },
            {
              "description": "Low Season",
              "priority": 500,
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2020-04-26",
                  "endDate": "2020-06-30",
                  "arrivalDays": [],
                  "departureDays": []
                }
              ],
              "minimumNights": "3",
              "bookableType": null,
              "rate": {
                "type": "nightly",
                "amount": 1020,
                "damageDeposit": null,
                "calculationMethod": "fixed",
                "calculationOperand": "equals",
                "applicableTaxes": []
              }
            },
            {
              "description": "High Season",
              "priority": 500,
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2020-07-01",
                  "endDate": "2020-08-31",
                  "arrivalDays": [],
                  "departureDays": []
                }
              ],
              "minimumNights": "3",
              "bookableType": null,
              "rate": {
                "type": "nightly",
                "amount": 1170,
                "calculationMethod": "fixed",
                "calculationOperand": "equals",
                "applicableTaxes": []
              }
            },
            {
              "description": "Low Season",
              "priority": 500,
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2020-09-01",
                  "endDate": "2020-09-25",
                  "arrivalDays": [],
                  "departureDays": []
                }
              ],
              "minimumNights": "3",
              "bookableType": null,
              "rate": {
                "type": "nightly",
                "amount": 1020,
                "damageDeposit": null,
                "calculationMethod": "fixed",
                "calculationOperand": "equals",
                "applicableTaxes": []
              }
            },
            {
              "description": "Chinese Golden week ",
              "priority": 500,
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2020-09-26",
                  "endDate": "2020-10-07",
                  "arrivalDays": [],
                  "departureDays": []
                }
              ],
              "minimumNights": "7",
              "bookableType": null,
              "rate": {
                "type": "nightly",
                "amount": 2240,
                "calculationMethod": "fixed",
                "calculationOperand": "equals",
                "applicableTaxes": []
              }
            },
            {
              "description": "Low Season",
              "priority": 500,
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2020-10-08",
                  "endDate": "2020-12-10",
                  "arrivalDays": [],
                  "departureDays": []
                }
              ],
              "minimumNights": "3",
              "bookableType": null,
              "rate": {
                "type": "nightly",
                "amount": 1020,
                "damageDeposit": null,
                "calculationMethod": "fixed",
                "calculationOperand": "equals",
                "applicableTaxes": []
              }
            },
            {
              "description": "Peak Season",
              "priority": 500,
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2020-12-11",
                  "endDate": "2020-12-17",
                  "arrivalDays": [],
                  "departureDays": []
                }
              ],
              "minimumNights": "5",
              "bookableType": null,
              "rate": {
                "type": "nightly",
                "amount": 2240,
                "calculationMethod": "fixed",
                "calculationOperand": "equals",
                "applicableTaxes": []
              }
            },
            {
              "description": "Christmas Season",
              "priority": 500,
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2020-12-18",
                  "endDate": "2021-01-10",
                  "arrivalDays": [],
                  "departureDays": []
                }
              ],
              "minimumNights": "7",
              "bookableType": null,
              "rate": {
                "type": "nightly",
                "amount": 2240,
                "calculationMethod": "fixed",
                "calculationOperand": "equals",
                "applicableTaxes": []
              }
            },
            {
              "description": "High Season ",
              "priority": 500,
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2021-01-11",
                  "endDate": "2021-02-10",
                  "arrivalDays": [],
                  "departureDays": []
                }
              ],
              "minimumNights": "5",
              "bookableType": null,
              "rate": {
                "type": "nightly",
                "amount": 1170,
                "calculationMethod": "fixed",
                "calculationOperand": "equals",
                "applicableTaxes": []
              }
            },
            {
              "description": "Chinese New Year ",
              "priority": 500,
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2021-02-11",
                  "endDate": "2021-02-17",
                  "arrivalDays": [],
                  "departureDays": []
                }
              ],
              "minimumNights": "5",
              "bookableType": null,
              "rate": {
                "type": "nightly",
                "amount": 2240,
                "damageDeposit": null,
                "calculationMethod": "fixed",
                "calculationOperand": "equals",
                "applicableTaxes": []
              }
            },
            {
              "description": "High Season ",
              "priority": 500,
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2021-02-18",
                  "endDate": "2021-04-02",
                  "arrivalDays": [],
                  "departureDays": []
                }
              ],
              "minimumNights": "5",
              "bookableType": null,
              "rate": {
                "type": "nightly",
                "amount": 1170,
                "calculationMethod": "fixed",
                "calculationOperand": "equals",
                "applicableTaxes": []
              }
            },
            {
              "description": "Peak Season",
              "priority": 500,
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2021-04-03",
                  "endDate": "2021-04-20",
                  "arrivalDays": [],
                  "departureDays": []
                }
              ],
              "minimumNights": "5",
              "bookableType": null,
              "rate": {
                "type": "nightly",
                "amount": 2240,
                "damageDeposit": null,
                "calculationMethod": "fixed",
                "calculationOperand": "equals",
                "applicableTaxes": []
              }
            },
            {
              "description": "Low Season",
              "priority": 500,
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2021-04-21",
                  "endDate": "2021-06-30",
                  "arrivalDays": [],
                  "departureDays": []
                }
              ],
              "minimumNights": "3",
              "bookableType": null,
              "rate": {
                "type": "nightly",
                "amount": 1020,
                "damageDeposit": null,
                "calculationMethod": "fixed",
                "calculationOperand": "equals",
                "applicableTaxes": []
              }
            }
          ],
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
                  "659e1307-d795-4d90-b8ef-2e2a4365f8e3"
                ]
              }
            },
            {
              "type": "extra_guest_fee",
              "hidden": false,
              "splitMethod": "ON_TOTAL",
              "priceGroup": "total",
              "description": "Extra guest",
              "conditionOperand": "AND",
              "conditions": [
                {
                  "type": "guests",
                  "modifyRatePerUnit": true,
                  "minimum": 8,
                  "maximum": 9
                },
                {
                  "type": "nights",
                  "modifyRatePerUnit": true
                }
              ],
              "rate": {
                "type": "adjustment",
                "amount": 50,
                "calculationMethod": "fixed",
                "calculationOperand": "addition",
                "taxable": true,
                "applicableTaxes": [
                  "659e1307-d795-4d90-b8ef-2e2a4365f8e3"
                ]
              }
            }
          ]
        }
      ]
    }';

    public function testReverseCalculationWorks()
    {

        $upp = new Upp(
            new HashMapPricingResolver(),
            new TestTranslator()
        );

        $options = new StructureOptions();

        $config = $upp->parsePricingConfig(json_decode($this->config, true), $options);

        $cc = $config->getCurrencyConfig('USD');

        $context = new PricingContext();
        $context->setCurrency('USD');
        $context->setBookingDate('2020-03-05');
        $context->setArrivalDate('2020-06-01');
        $context->setDepartureDate('2020-06-02');
        $context->setGuests(2);

        //$price = $upp->generatePrice($context, $config);


    }

}