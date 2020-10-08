<?php

namespace Aptenex\Upp\Tests;

use Aptenex\Upp\Parser\TaxesParser;
use Aptenex\Upp\Upp;
use PHPUnit\Framework\TestCase;
use Aptenex\Upp\Translation\TestTranslator;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Parser\Structure\Defaults;
use Aptenex\Upp\Parser\ExternalConfig\ConfigOverrideCommand;
use Aptenex\Upp\Parser\Structure\StructureOptions;
use Aptenex\Upp\Parser\Resolver\HashMapPricingResolver;
use Aptenex\Upp\Parser\ExternalConfig\ExternalCommandDirector;

class TaxesTest extends TestCase
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
          "taxes": [
            
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

    public function testPercentageTax()
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

        $taxesJson = '[{
              "name": "People Tax",
              "uuid": "example-people-tax-uuid",
              "amount": 0.1,
              "calculationMethod": "percentage"
        }]';

        $config->getCurrencyConfig('GBP')->setTaxes((new TaxesParser(null))->parse(json_decode($taxesJson, true)));

        $pricing = $upp->generatePrice($context, $config);

        // 1100.0
        $this->assertSame('110000', $pricing->getTotal()->getAmount());
    }

    public function testFixedTax()
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

        $taxesJson = '[{
              "name": "People Tax",
              "uuid": "example-people-tax-uuid",
              "amount": 200,
              "calculationMethod": "fixed"
        }]';

        $config->getCurrencyConfig('GBP')->setTaxes((new TaxesParser(null))->parse(json_decode($taxesJson, true)));

        $pricing = $upp->generatePrice($context, $config);

        // 1100.0
        $this->assertSame('120000', $pricing->getTotal()->getAmount());
    }

    public function testFlatPerNightTax()
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

        $taxesJson = '[{
              "name": "People Tax",
              "uuid": "example-people-tax-uuid",
              "amount": 50,
              "calculationMethod": "flat_per_night"
        }]';

        $config->getCurrencyConfig('GBP')->setTaxes((new TaxesParser(null))->parse(json_decode($taxesJson, true)));

        $pricing = $upp->generatePrice($context, $config);

        // 1100.0
        $this->assertSame('150000', $pricing->getTotal()->getAmount());
    }

    public function testFlatPerGuestTax()
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
        $context->setGuests(3);

        $config = $upp->parsePricingConfig(json_decode(self::$json1, true), $options);

        $taxesJson = '[{
              "name": "People Tax",
              "uuid": "example-people-tax-uuid",
              "amount": 50,
              "calculationMethod": "flat_per_guest"
        }]';

        $config->getCurrencyConfig('GBP')->setTaxes((new TaxesParser(null))->parse(json_decode($taxesJson, true)));

        $pricing = $upp->generatePrice($context, $config);

        // 1100.0
        $this->assertSame('115000', $pricing->getTotal()->getAmount());
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

        $taxesJson = '[{
              "name": "People Tax",
              "uuid": "example-people-tax-uuid",
              "amount": 50,
              "calculationMethod": "flat_per_guest_per_night"
        }]';

        $config->getCurrencyConfig('GBP')->setTaxes((new TaxesParser(null))->parse(json_decode($taxesJson, true)));

        $pricing = $upp->generatePrice($context, $config);

        // 1100.0
        $this->assertSame('200000', $pricing->getTotal()->getAmount());
    }

    public function testLongStayExemption()
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

        $taxesJson = '[{
              "name": "People Tax",
              "uuid": "example-people-tax-uuid",
              "amount": 50,
              "calculationMethod": "flat_per_guest_per_night",
              "longStayExemption": 5
        }]';

        $config->getCurrencyConfig('GBP')->setTaxes((new TaxesParser(null))->parse(json_decode($taxesJson, true)));

        $pricing = $upp->generatePrice($context, $config);

        // 1100.0
        $this->assertSame('100000', $pricing->getTotal()->getAmount());
    }

}