<?php

namespace Aptenex\Upp\Tests;

use Aptenex\Upp\Upp;
use PHPUnit\Framework\TestCase;
use Aptenex\Upp\Parser\ModifiersParser;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Parser\Structure\Defaults;
use Aptenex\Upp\Translation\TestTranslator;
use Aptenex\Upp\Parser\Structure\StructureOptions;
use Aptenex\Upp\Parser\Resolver\HashMapPricingResolver;
use Aptenex\Upp\Parser\ExternalConfig\ConfigOverrideCommand;
use Aptenex\Upp\Parser\ExternalConfig\ExternalCommandDirector;

class ModifiersTest extends TestCase
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
                  "startDate": "2016-06-26",
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

    public function testPercentageModifier()
    {
        $upp = new Upp(
            new HashMapPricingResolver(),
            new TestTranslator()
        );

        $defaults = new Defaults();
        $defaults->setDaysRequiredInAdvanceForBooking(1);

        $options = new StructureOptions();
        $options->setExternalCommandDirector(new ExternalCommandDirector([
            new ConfigOverrideCommand([
                'defaults' => $defaults
            ])
        ]));

        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2017-05-01');
        $context->setArrivalDate('2017-06-20');
        $context->setDepartureDate('2017-06-30');
        $context->setGuests(2);

        $config = $upp->parsePricingConfig(json_decode(self::$json1, true), new StructureOptions());

        $json = '[
            {
                "type": "modifier",
                "hidden": false,
                "splitMethod": "ON_TOTAL",
                "priceGroup": "total",
                "description": "New Modifier 1",
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
        ]';

        $config->getCurrencyConfig('GBP')->setModifiers((new ModifiersParser(null))->parse(json_decode($json, true), new StructureOptions()));

        $pricing = $upp->generatePrice($context, $config);

        // 1100.0
        $this->assertSame('110000', $pricing->getTotal()->getAmount());
    }

    public function testFixedModifier()
    {
        $upp = new Upp(
            new HashMapPricingResolver(),
            new TestTranslator()
        );

        $defaults = new Defaults();
        $defaults->setDaysRequiredInAdvanceForBooking(1);

        $options = new StructureOptions();
        $options->setExternalCommandDirector(new ExternalCommandDirector([
            new ConfigOverrideCommand([
                'defaults' => $defaults
            ])
        ]));

        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2017-05-01');
        $context->setArrivalDate('2017-06-20');
        $context->setDepartureDate('2017-06-30');
        $context->setGuests(2);

        $config = $upp->parsePricingConfig(json_decode(self::$json1, true), $options);

        $json = '[
            {
                "type": "modifier",
                "hidden": false,
                "splitMethod": "ON_TOTAL",
                "priceGroup": "total",
                "description": "New Modifier 1",
                "conditionOperand": "AND",
                "conditions": [],
                "rate": {
                    "type": "adjustment",
                    "amount": 200,
                    "calculationMethod": "fixed",
                    "calculationOperand": "addition",
                    "applicableTaxes": [],
                    "daysOfWeek": null
                }
            }
        ]';

        $config->getCurrencyConfig('GBP')->setModifiers((new ModifiersParser(null))->parse(json_decode($json, true), new StructureOptions()));

        $pricing = $upp->generatePrice($context, $config);

        // 1100.0
        $this->assertSame('120000', $pricing->getTotal()->getAmount());
    }

    public function testFlatPerNightModifier()
    {
        $upp = new Upp(
            new HashMapPricingResolver(),
            new TestTranslator()
        );

        $defaults = new Defaults();
        $defaults->setDaysRequiredInAdvanceForBooking(1);

        $options = new StructureOptions();
        $options->setExternalCommandDirector(new ExternalCommandDirector([
            new ConfigOverrideCommand([
                'defaults' => $defaults
            ])
        ]));

        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2017-05-01');
        $context->setArrivalDate('2017-06-20');
        $context->setDepartureDate('2017-06-30');
        $context->setGuests(2);

        $config = $upp->parsePricingConfig(json_decode(self::$json1, true), $options);

        $json = '[
            {
                "type": "modifier",
                "hidden": false,
                "splitMethod": "ON_TOTAL",
                "priceGroup": "total",
                "description": "New Modifier 1",
                "conditionOperand": "AND",
                "conditions": [],
                "rate": {
                    "type": "adjustment",
                    "amount": 50,
                    "calculationMethod": "flat_per_night",
                    "calculationOperand": "addition",
                    "applicableTaxes": [],
                    "daysOfWeek": null
                }
            }
        ]';

        $config->getCurrencyConfig('GBP')->setModifiers((new ModifiersParser(null))->parse(json_decode($json, true), new StructureOptions()));

        $pricing = $upp->generatePrice($context, $config);

        // 1100.0
        $this->assertSame('150000', $pricing->getTotal()->getAmount());
    }

    public function testFlatPerGuestModifier()
    {
        $upp = new Upp(
            new HashMapPricingResolver(),
            new TestTranslator()
        );

        $defaults = new Defaults();
        $defaults->setDaysRequiredInAdvanceForBooking(1);

        $options = new StructureOptions();
        $options->setExternalCommandDirector(new ExternalCommandDirector([
            new ConfigOverrideCommand([
                'defaults' => $defaults
            ])
        ]));

        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2017-05-01');
        $context->setArrivalDate('2017-06-20');
        $context->setDepartureDate('2017-06-30');
        $context->setGuests(2);

        $config = $upp->parsePricingConfig(json_decode(self::$json1, true), $options);

        $json = '[
            {
                "type": "modifier",
                "hidden": false,
                "splitMethod": "ON_TOTAL",
                "priceGroup": "total",
                "description": "New Modifier 1",
                "conditionOperand": "AND",
                "conditions": [],
                "rate": {
                    "type": "adjustment",
                    "amount": 50,
                    "calculationMethod": "flat_per_guest",
                    "calculationOperand": "addition",
                    "applicableTaxes": [],
                    "daysOfWeek": null
                }
            }
        ]';

        $config->getCurrencyConfig('GBP')->setModifiers((new ModifiersParser(null))->parse(json_decode($json, true), new StructureOptions()));

        $pricing = $upp->generatePrice($context, $config);

        // 1100.0
        $this->assertSame('110000', $pricing->getTotal()->getAmount());
    }

    public function testFlatPerGuestPerNight()
    {
        $upp = new Upp(
            new HashMapPricingResolver(),
            new TestTranslator()
        );

        $defaults = new Defaults();
        $defaults->setDaysRequiredInAdvanceForBooking(1);

        $options = new StructureOptions();
        $options->setExternalCommandDirector(new ExternalCommandDirector([
            new ConfigOverrideCommand([
                'defaults' => $defaults
            ])
        ]));

        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2017-05-01');
        $context->setArrivalDate('2017-06-20');
        $context->setDepartureDate('2017-06-30');
        $context->setGuests(2);

        $config = $upp->parsePricingConfig(json_decode(self::$json1, true), $options);

        $json = '[
            {
                "type": "modifier",
                "hidden": false,
                "splitMethod": "ON_TOTAL",
                "priceGroup": "total",
                "description": "New Modifier 1",
                "conditionOperand": "AND",
                "conditions": [],
                "rate": {
                    "type": "adjustment",
                    "amount": 50,
                    "calculationMethod": "flat_per_guest_per_night",
                    "calculationOperand": "addition",
                    "applicableTaxes": [],
                    "daysOfWeek": null
                }
            }
        ]';

        $config->getCurrencyConfig('GBP')->setModifiers((new ModifiersParser(null))->parse(json_decode($json, true), new StructureOptions()));

        $pricing = $upp->generatePrice($context, $config);

        // 1100.0
        $this->assertSame('200000', $pricing->getTotal()->getAmount());
    }

    public function testFlatPerGuestPerNightWithMinimumOver()
    {
        $upp = new Upp(
            new HashMapPricingResolver(),
            new TestTranslator()
        );

        $defaults = new Defaults();
        $defaults->setDaysRequiredInAdvanceForBooking(1);

        $options = new StructureOptions();
        $options->setExternalCommandDirector(new ExternalCommandDirector([
            new ConfigOverrideCommand([
                'defaults' => $defaults
            ])
        ]));

        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2017-05-01');
        $context->setArrivalDate('2017-06-20');
        $context->setDepartureDate('2017-06-30');
        $context->setGuests(6);

        $config = $upp->parsePricingConfig(json_decode(self::$json1, true), $options);

        $json = '[
            {
                "type": "modifier",
                "hidden": false,
                "splitMethod": "ON_TOTAL",
                "priceGroup": "total",
                "description": "New Modifier 1",
                "conditionOperand": "AND",
                "conditions": [],
                "rate": {
                    "type": "adjustment",
                    "amount": 50,
                    "calculationMethod": "flat_per_guest_per_night",
                    "calculationOperand": "addition",
                    "applyOverMinimumGuests": 4,
                    "applicableTaxes": [],
                    "daysOfWeek": null
                }
            }
        ]';

        $config->getCurrencyConfig('GBP')->setModifiers((new ModifiersParser(null))->parse(json_decode($json, true), new StructureOptions()));

        $pricing = $upp->generatePrice($context, $config);

        // 1100.0
        $this->assertSame('200000', $pricing->getTotal()->getAmount());
    }

}