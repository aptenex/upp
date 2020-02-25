<?php

namespace Tests;

use Aptenex\Upp\Parser\ExternalConfig\NightlyTariffOverrideCommand;
use Aptenex\Upp\Parser\ExternalConfig\StrategyRemovalCommand;
use Aptenex\Upp\Parser\Structure\ExtraNightsAlteration;
use Aptenex\Upp\Parser\Structure\RateStrategy;
use Aptenex\Upp\Upp;
use Aptenex\Upp\Util\MoneyUtils;
use PHPUnit\Framework\TestCase;
use Translation\TestTranslator;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Parser\Structure\Defaults;
use Parser\ExternalConfig\ConfigOverrideCommand;
use Aptenex\Upp\Parser\Structure\StructureOptions;
use Aptenex\Upp\Parser\Resolver\HashMapPricingResolver;
use Aptenex\Upp\Parser\ExternalConfig\ExternalCommandDirector;

class StrategyRemovalTest extends TestCase
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
                  "startDate": "2017-06-26",
                  "endDate": "2020-06-30",
                  "arrivalDays": [],
                  "departureDays": []
                }
              ],
              "rate": {
                "type": "nightly",
                "amount": 250,
                "calculationMethod": "fixed",
                "calculationOperand": "equals",
                "strategy": {
                  "extraNightsAlteration": {
                    "applyToTotal": false,
                    "calculationMethod": "fixed",
                    "makePreviousDaysSameRate": true,
                    "brackets": [
                      {
                        "night": 1,
                        "amount": 100
                      },
                      {
                        "night": "2",
                        "amount": 90
                      },
                      {
                        "night": "3-5",
                        "amount": 80
                      },
                      {
                        "night": "6",
                        "amount": 35
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

    public function testStrategyRemoval()
    {
        $upp = new Upp(
            new HashMapPricingResolver(),
            new TestTranslator()
        );

        $defaults = new Defaults();
        $defaults->setDaysRequiredInAdvanceForBooking(1);

        $options = new StructureOptions();
        $options->setExternalCommandDirector(new ExternalCommandDirector([
            new StrategyRemovalCommand()
        ]));

        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2017-05-01');
        $context->setArrivalDate('2017-06-26');
        $context->setDepartureDate('2017-06-30');
        $context->setGuests(3);

        $config = $upp->parsePricingConfig(json_decode(self::$json1, true), $options);
        $this->assertNull($config->getCurrencyConfig('GBP')->getPeriods()[0]->getRate()->getStrategy());

        $configWith = $upp->parsePricingConfig(json_decode(self::$json1, true), new StructureOptions());
        $this->assertInstanceOf(
            RateStrategy::class,
            $configWith->getCurrencyConfig('GBP')->getPeriods()[0]->getRate()->getStrategy()
        );
    }

}