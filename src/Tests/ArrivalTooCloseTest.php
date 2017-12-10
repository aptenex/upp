<?php

namespace Aptenex\Upp\Tests;

use Aptenex\Upp\Upp;
use App\Entity\Organization;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Parser\Structure\StructureOptions;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Aptenex\Upp\Parser\ExternalConfig\ExternalCommandDirector;
use App\Library\Upp\Parser\ExternalConfig\OrganizationGlobalSettingsCommand;

class ArrivalTooCloseTest extends WebTestCase
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

    public function testSettingActiveNoError()
    {
        $client = static::createClient();

        $upp = new Upp(
            $client->getContainer()->get('app.property_manager_factory')->getDoctrinePricingResolver(),
            $client->getContainer()->get('translator')
        );

        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2017-05-01');
        $context->setArrivalDate('2017-06-26');
        $context->setDepartureDate('2017-06-30');
        $context->setGuests(3);

        $newOrg = new Organization();
        $newOrg->setDaysRequiredInAdvanceForBooking(1);

        $options = new StructureOptions();
        $options->setExternalCommandDirector(new ExternalCommandDirector([
            new OrganizationGlobalSettingsCommand($newOrg)
        ]));

        $config = $upp->parsePricingConfig(json_decode(self::$json1, true), $options);

        $pricing = $upp->generatePrice($context, $config);

        $this->assertTrue($pricing instanceof FinalPrice);
    }

    /**
     * @expectedException \Aptenex\Upp\Exception\CannotBookDatesException
     */
    public function testTwoDaysNotAllowed()
    {
        $client = static::createClient();

        $upp = new Upp(
            $client->getContainer()->get('app.property_manager_factory')->getDoctrinePricingResolver(),
            $client->getContainer()->get('translator')
        );

        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2017-06-26');
        $context->setArrivalDate('2017-06-27');
        $context->setDepartureDate('2017-06-30');
        $context->setGuests(3);

        $newOrg = new Organization();
        $newOrg->setDaysRequiredInAdvanceForBooking(2);

        $options = new StructureOptions();
        $options->setExternalCommandDirector(new ExternalCommandDirector([
            new OrganizationGlobalSettingsCommand($newOrg)
        ]));

        $config = $upp->parsePricingConfig(json_decode(self::$json1, true), $options);

        $pricing = $upp->generatePrice($context, $config);
    }

    public function testNextDayAllowed()
    {
        $client = static::createClient();

        $upp = new Upp(
            $client->getContainer()->get('app.property_manager_factory')->getDoctrinePricingResolver(),
            $client->getContainer()->get('translator')
        );

        $context = new PricingContext();
        $context->setCurrency('GBP');
        $context->setBookingDate('2017-06-26');
        $context->setArrivalDate('2017-06-27');
        $context->setDepartureDate('2017-06-30');
        $context->setGuests(3);

        $newOrg = new Organization();
        $newOrg->setDaysRequiredInAdvanceForBooking(1);

        $options = new StructureOptions();
        $options->setExternalCommandDirector(new ExternalCommandDirector([
            new OrganizationGlobalSettingsCommand($newOrg)
        ]));

        $config = $upp->parsePricingConfig(json_decode(self::$json1, true), $options);

        $pricing = $upp->generatePrice($context, $config);

        $this->assertTrue($pricing instanceof FinalPrice);
    }

}