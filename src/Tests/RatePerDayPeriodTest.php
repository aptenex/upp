<?php

namespace Tests;

use Aptenex\Upp\Calculation\Night;
use Aptenex\Upp\Parser\ExternalConfig\NightlyTariffOverrideCommand;
use Aptenex\Upp\Upp;
use Aptenex\Upp\Util\MoneyUtils;
use PHPUnit\Framework\TestCase;
use Aptenex\Upp\Translation\TestTranslator;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Parser\Structure\Defaults;
use Aptenex\Upp\Parser\ExternalConfig\ConfigOverrideCommand;
use Aptenex\Upp\Parser\Structure\StructureOptions;
use Aptenex\Upp\Parser\Resolver\HashMapPricingResolver;
use Aptenex\Upp\Parser\ExternalConfig\ExternalCommandDirector;

class RatePerDayPeriodTest extends TestCase
{

    private $json1 = '{
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
              "minimumNights": 2,
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2017-01-01",
                  "endDate": "2020-12-31",
                  "arrivalDays": [],
                  "departureDays": ["monday"]
                }
              ],
              "rate": {
                "type": "nightly",
                "amount": 250,
                "calculationMethod": "fixed",
                "calculationOperator": "equals",
                "daysOfWeek": {
                    "calculationMethod": "fixed",
                    "days": {
                        "monday": {
                            "amount": 0,
                            "changeover": "ARRIVAL_OR_DEPARTURE",
                            "minimumNights": null
                        },
                        "tuesday": {
                            "amount": null,
                            "changeover": "ARRIVAL_OR_DEPARTURE",
                            "minimumNights": null
                        },
                        "wednesday": {
                            "amount": 0,
                            "changeover": "ARRIVAL_OR_DEPARTURE",
                            "minimumNights": null
                        },
                        "thursday": {
                            "amount": 0,
                            "changeover": "ARRIVAL_OR_DEPARTURE",
                            "minimumNights": null
                        },
                        "friday": {
                            "amount": 300,
                            "changeover": "ARRIVAL_ONLY",
                            "minimumNights": 3
                        },
                        "saturday": {
                            "amount": 300,
                            "changeover": "NONE",
                            "minimumNights": 3
                        },
                        "sunday": {
                            "amount": 300,
                            "changeover": "DEPARTURE_ONLY",
                            "minimumNights": 3
                        }
                    }
                }
              }
            },
            {
              "description": "Standard",
              "priority": 500,
              "conditionOperand": "AND",
              "bookableType": null,
              "minimumNights": 2,
              "conditions": [
                {
                  "type": "date",
                  "modifyRatePerUnit": false,
                  "startDate": "2021-01-01",
                  "endDate": "2030-12-31",
                  "arrivalDays": [],
                  "departureDays": ["monday"]
                }
              ],
              "rate": {
                "type": "nightly",
                "amount": 100,
                "calculationMethod": "fixed",
                "calculationOperator": "equals",
                "daysOfWeek": {
                    "calculationMethod": "percentage",
                    "days": {
                        "monday": {
                            "amount": 0.75,
                            "changeover": "ARRIVAL_OR_DEPARTURE",
                            "minimumNights": null
                        },
                        "tuesday": {
                            "amount": 0.75,
                            "changeover": "ARRIVAL_OR_DEPARTURE",
                            "minimumNights": null
                        },
                        "wednesday": {
                            "amount": 0.75,
                            "changeover": "ARRIVAL_OR_DEPARTURE",
                            "minimumNights": null
                        },
                        "thursday": {
                            "amount": 0.75,
                            "changeover": "ARRIVAL_OR_DEPARTURE",
                            "minimumNights": null
                        },
                        "friday": {
                            "amount": 1.1,
                            "changeover": "ARRIVAL_OR_DEPARTURE",
                            "minimumNights": 3
                        },
                        "saturday": {
                            "amount": 1.1,
                            "changeover": "NONE",
                            "minimumNights": 3
                        },
                        "sunday": {
                            "amount": 1.1,
                            "changeover": "DEPARTURE_ONLY",
                            "minimumNights": 3
                        }
                    }
                }
              }
            }
          ],
          "modifiers": []
        }
      ]
    }';

    public function testDaysOfWeekSuccessful()
    {
        $upp = new Upp(
            new HashMapPricingResolver(),
            new TestTranslator()
        );

        $defaults = new Defaults();
        $defaults->setDaysRequiredInAdvanceForBooking(1);

        $options = new StructureOptions();
        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2017-05-01');
        $context->setArrivalDate('2017-05-10');
        $context->setDepartureDate('2017-05-21');
        $context->setGuests(3);

        $config = $upp->parsePricingConfig(json_decode($this->json1, true), $options);

        $pricing = $upp->generatePrice($context, $config);

        $this->assertInstanceOf(FinalPrice::class, $pricing);
        $this->assertSame(3000.00, MoneyUtils::getConvertedAmount($pricing->getTotal()));

        $nights = array_values($pricing->getStay()->getNights());

        $this->assertSame(250.0, MoneyUtils::getConvertedAmount($nights[0]->getCost()));
        $this->assertSame(250.0, MoneyUtils::getConvertedAmount($nights[1]->getCost()));
        $this->assertSame(300.0, MoneyUtils::getConvertedAmount($nights[2]->getCost()));
        $this->assertSame(300.0, MoneyUtils::getConvertedAmount($nights[3]->getCost()));
        $this->assertSame(300.0, MoneyUtils::getConvertedAmount($nights[4]->getCost()));
        $this->assertSame(250.0, MoneyUtils::getConvertedAmount($nights[5]->getCost()));
        $this->assertSame(250.0, MoneyUtils::getConvertedAmount($nights[6]->getCost()));
        $this->assertSame(250.0, MoneyUtils::getConvertedAmount($nights[7]->getCost()));
        $this->assertSame(250.0, MoneyUtils::getConvertedAmount($nights[8]->getCost()));
        $this->assertSame(300.0, MoneyUtils::getConvertedAmount($nights[9]->getCost()));
        $this->assertSame(300.0, MoneyUtils::getConvertedAmount($nights[10]->getCost()));

        $context2 = new PricingContext();
        $context2->setCurrency('GBP');
        $context2->setBookingDate('2017-05-01');
        $context2->setArrivalDate('2021-05-10');
        $context2->setDepartureDate('2021-05-21');
        $context2->setGuests(3);

        $pricing2 = $upp->generatePrice($context2, $config);

        $this->assertInstanceOf(FinalPrice::class, $pricing2);
        $this->assertSame(930.0, MoneyUtils::getConvertedAmount($pricing2->getTotal()));

        $nights = array_values($pricing2->getStay()->getNights());

        $this->assertSame(75.0, MoneyUtils::getConvertedAmount($nights[0]->getCost()));
        $this->assertSame(75.0, MoneyUtils::getConvertedAmount($nights[1]->getCost()));
        $this->assertSame(75.0, MoneyUtils::getConvertedAmount($nights[2]->getCost()));
        $this->assertSame(75.0, MoneyUtils::getConvertedAmount($nights[3]->getCost()));
        $this->assertSame(110.0, MoneyUtils::getConvertedAmount($nights[4]->getCost()));
        $this->assertSame(110.0, MoneyUtils::getConvertedAmount($nights[5]->getCost()));
        $this->assertSame(110.0, MoneyUtils::getConvertedAmount($nights[6]->getCost()));
        $this->assertSame(75.0, MoneyUtils::getConvertedAmount($nights[7]->getCost()));
        $this->assertSame(75.0, MoneyUtils::getConvertedAmount($nights[8]->getCost()));
        $this->assertSame(75.0, MoneyUtils::getConvertedAmount($nights[9]->getCost()));
        $this->assertSame(75.0, MoneyUtils::getConvertedAmount($nights[10]->getCost()));
    }

    /**
     * @throws \Aptenex\Upp\Exception\InvalidPricingConfigException
     *
     * @expectedException \Aptenex\Upp\Exception\CannotMatchRequestedDatesException
     */
    public function testMinimumNightsWork()
    {
        $upp = new Upp(
            new HashMapPricingResolver(),
            new TestTranslator()
        );

        $defaults = new Defaults();
        $defaults->setDaysRequiredInAdvanceForBooking(1);

        $options = new StructureOptions();
        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2017-05-01');
        $context->setArrivalDate('2020-03-27');
        $context->setDepartureDate('2020-03-29');
        $context->setGuests(3);

        $config = $upp->parsePricingConfig(json_decode($this->json1, true), $options);

        $pricing = $upp->generatePrice($context, $config);
    }

    /**
     * @throws \Aptenex\Upp\Exception\InvalidPricingConfigException
     *
     * @expectedException \Aptenex\Upp\Exception\CannotMatchRequestedDatesException
     */
    public function testInvalidChangeover()
    {
        $upp = new Upp(
            new HashMapPricingResolver(),
            new TestTranslator()
        );

        $defaults = new Defaults();
        $defaults->setDaysRequiredInAdvanceForBooking(1);

        $options = new StructureOptions();
        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2017-05-01');
        $context->setArrivalDate('2020-03-27');
        $context->setDepartureDate('2020-04-04');
        $context->setGuests(3);

        $config = $upp->parsePricingConfig(json_decode($this->json1, true), $options);

        $pricing = $upp->generatePrice($context, $config);
    }

}