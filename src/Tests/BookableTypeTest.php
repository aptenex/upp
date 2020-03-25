<?php

namespace Aptenex\Upp\Tests;

use Aptenex\Upp\Parser\Resolver\HashMapPricingResolver;
use Aptenex\Upp\Upp;
use PHPUnit\Framework\TestCase;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Parser\Structure\Period;
use Aptenex\Upp\Translation\TestTranslator;

class BookableTypeTest extends TestCase
{

    public function testBookableDefaultType()
    {
        $jsonConfig1 = '{
          "name": "Pricing",
          "schema": "property-pricing",
          "version": "0.0.1",
          "meta": [],
          "data": [
            {
              "currency": "GBP",
              "defaults": {
                "damageDeposit": 100,
                "damageDepositCalculationMethod": "fixed",
                "minimumNights": 3,
                "bookableType": null,
                "balanceDaysBeforeArrival": 0,
                "depositSplitPercentage": 30,
                "extraNightAlterationStrategyUseGlobalNights": false,
                "damageDepositSplitMethod": "ON_DEPOSIT"
              },
              "taxes": [
                {
                  "name": "People Tax",
                  "uuid": "example-people-tax-uuid",
                  "amount": 0.1,
                  "calculationMethod": "percentage"
                }
              ],
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
                      "startDate": "2017-06-26",
                      "endDate": "2020-06-30",
                      "arrivalDays": [],
                      "departureDays": []
                    }
                  ],
                  "rate": {
                    "type": "nightly",
                    "amount": 100,
                    "calculationMethod": "fixed",
                    "calculationOperand": "equals"
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

        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2017-05-01');
        $context->setArrivalDate('2017-06-26');
        $context->setDepartureDate('2017-06-30');
        $context->setGuests(3);

        $config = $upp->parsePricingConfig(json_decode($jsonConfig1, true));

        $pricing = $upp->generatePrice($context, $config);

        $this->assertSame(Period::BOOKABLE_TYPE_INSTANT_BOOKABLE, $pricing->getBookableType());
    }

    public function testBookableTypeDefaultPassthrough()
    {
        $jsonConfig1 = '{
          "name": "Pricing",
          "schema": "property-pricing",
          "version": "0.0.1",
          "meta": [],
          "data": [
            {
              "currency": "GBP",
              "defaults": {
                "damageDeposit": 100,
                "damageDepositCalculationMethod": "fixed",
                "minimumNights": 3,
                "bookableType": "enquiry_only",
                "balanceDaysBeforeArrival": 0,
                "depositSplitPercentage": 30,
                "extraNightAlterationStrategyUseGlobalNights": false,
                "damageDepositSplitMethod": "ON_DEPOSIT"
              },
              "taxes": [
                {
                  "name": "People Tax",
                  "uuid": "example-people-tax-uuid",
                  "amount": 0.1,
                  "calculationMethod": "percentage"
                }
              ],
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
                      "startDate": "2017-06-26",
                      "endDate": "2020-06-30",
                      "arrivalDays": [],
                      "departureDays": []
                    }
                  ],
                  "rate": {
                    "type": "nightly",
                    "amount": 100,
                    "calculationMethod": "fixed",
                    "calculationOperand": "equals"
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

        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2017-05-01');
        $context->setArrivalDate('2017-06-26');
        $context->setDepartureDate('2017-06-30');
        $context->setGuests(3);

        $config = $upp->parsePricingConfig(json_decode($jsonConfig1, true));

        $pricing = $upp->generatePrice($context, $config);

        $this->assertSame(Period::BOOKABLE_TYPE_ENQUIRY_ONLY, $pricing->getBookableType());
    }

    public function testBookableTypePeriodOverride()
    {
        $jsonConfig1 = '{
          "name": "Pricing",
          "schema": "property-pricing",
          "version": "0.0.1",
          "meta": [],
          "data": [
            {
              "currency": "GBP",
              "defaults": {
                "damageDeposit": 100,
                "damageDepositCalculationMethod": "fixed",
                "minimumNights": 3,
                "bookableType": "enquiry_only",
                "balanceDaysBeforeArrival": 0,
                "depositSplitPercentage": 30,
                "extraNightAlterationStrategyUseGlobalNights": false,
                "damageDepositSplitMethod": "ON_DEPOSIT"
              },
              "taxes": [
                {
                  "name": "People Tax",
                  "uuid": "example-people-tax-uuid",
                  "amount": 0.1,
                  "calculationMethod": "percentage"
                }
              ],
              "periods": [
                {
                  "description": "Standard",
                  "priority": 500,
                  "conditionOperand": "AND",
                  "bookableType": "instant_bookable",
                  "conditions": [
                    {
                      "type": "date",
                      "modifyRatePerUnit": false,
                      "startDate": "2017-06-26",
                      "endDate": "2020-06-30",
                      "arrivalDays": [],
                      "departureDays": []
                    }
                  ],
                  "rate": {
                    "type": "nightly",
                    "amount": 100,
                    "calculationMethod": "fixed",
                    "calculationOperand": "equals"
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

        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2017-05-01');
        $context->setArrivalDate('2017-06-26');
        $context->setDepartureDate('2017-06-30');
        $context->setGuests(3);

        $config = $upp->parsePricingConfig(json_decode($jsonConfig1, true));

        $pricing = $upp->generatePrice($context, $config);

        $this->assertSame(Period::BOOKABLE_TYPE_INSTANT_BOOKABLE, $pricing->getBookableType());
    }

    public function testPriorityOverMultiplePeriods()
    {
        $jsonConfig1 = '{
          "name": "Pricing",
          "schema": "property-pricing",
          "version": "0.0.1",
          "meta": [],
          "data": [
            {
              "currency": "GBP",
              "defaults": {
                "damageDeposit": 100,
                "damageDepositCalculationMethod": "fixed",
                "minimumNights": 3,
                "bookableType": null,
                "balanceDaysBeforeArrival": 0,
                "depositSplitPercentage": 30,
                "extraNightAlterationStrategyUseGlobalNights": false,
                "damageDepositSplitMethod": "ON_DEPOSIT"
              },
              "taxes": [
                {
                  "name": "People Tax",
                  "uuid": "example-people-tax-uuid",
                  "amount": 0.1,
                  "calculationMethod": "percentage"
                }
              ],
              "periods": [
                {
                  "description": "Standard 1",
                  "priority": 500,
                  "conditionOperand": "AND",
                  "bookableType": "instant_bookable",
                  "conditions": [
                    {
                      "type": "date",
                      "modifyRatePerUnit": false,
                      "startDate": "2017-06-01",
                      "endDate": "2017-06-05",
                      "arrivalDays": [],
                      "departureDays": []
                    }
                  ],
                  "rate": {
                    "type": "nightly",
                    "amount": 100,
                    "calculationMethod": "fixed",
                    "calculationOperand": "equals"
                  }
                },
                {
                  "description": "Standard 2",
                  "priority": 500,
                  "conditionOperand": "AND",
                  "bookableType": "enquiry_with_price",
                  "conditions": [
                    {
                      "type": "date",
                      "modifyRatePerUnit": false,
                      "startDate": "2017-06-06",
                      "endDate": "2017-06-10",
                      "arrivalDays": [],
                      "departureDays": []
                    }
                  ],
                  "rate": {
                    "type": "nightly",
                    "amount": 100,
                    "calculationMethod": "fixed",
                    "calculationOperand": "equals"
                  }
                },
                {
                  "description": "Standard 3",
                  "priority": 500,
                  "conditionOperand": "AND",
                  "bookableType": "enquiry_only",
                  "conditions": [
                    {
                      "type": "date",
                      "modifyRatePerUnit": false,
                      "startDate": "2017-06-11",
                      "endDate": "2017-06-20",
                      "arrivalDays": [],
                      "departureDays": []
                    }
                  ],
                  "rate": {
                    "type": "nightly",
                    "amount": 100,
                    "calculationMethod": "fixed",
                    "calculationOperand": "equals"
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

        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2017-05-01');
        $context->setArrivalDate('2017-06-01');
        $context->setDepartureDate('2017-06-04');
        $context->setGuests(3);

        $config = $upp->parsePricingConfig(json_decode($jsonConfig1, true));

        $pricing = $upp->generatePrice($context, $config);

        $this->assertSame(Period::BOOKABLE_TYPE_INSTANT_BOOKABLE, $pricing->getBookableType());

        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2017-05-01');
        $context->setArrivalDate('2017-06-01');
        $context->setDepartureDate('2017-06-08');
        $context->setGuests(3);

        $config = $upp->parsePricingConfig(json_decode($jsonConfig1, true));

        $pricing = $upp->generatePrice($context, $config);

        $this->assertSame(Period::BOOKABLE_TYPE_ENQUIRY_WITH_PRICE, $pricing->getBookableType());

        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2017-05-01');
        $context->setArrivalDate('2017-06-01');
        $context->setDepartureDate('2017-06-17');
        $context->setGuests(3);

        $config = $upp->parsePricingConfig(json_decode($jsonConfig1, true));

        $pricing = $upp->generatePrice($context, $config);

        $this->assertSame(Period::BOOKABLE_TYPE_ENQUIRY_ONLY, $pricing->getBookableType());
    }

}