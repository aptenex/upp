<?php

namespace Aptenex\Upp\Tests;

use App\Manager\Property\HashMapPricingResolver;
use Aptenex\Upp\Util\ArrayUtils;
use Aptenex\Upp\Upp;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Parser\Structure\StructureOptions;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MultipleCalculationTest extends WebTestCase
{

    private function getCurrentTestName($priceConfig, $testIndex, $testKey)
    {
        return vsprintf('testConfigs() // Name: %s %sTest Index: %s %sComparison: %s%s', [
            $priceConfig['name'],
            PHP_EOL,
            $testIndex,
            PHP_EOL,
            $testKey,
            PHP_EOL . PHP_EOL
        ]);
    }

    public function testConfigs()
    {
        $client = static::createClient();
        $kernel = $client->getContainer()->get('kernel');

        $priceConfigs = json_decode(file_get_contents(__DIR__ . '/Resources/test-configs.json'), true);

        $structureOptions = new StructureOptions();

        foreach($priceConfigs as $priceConfig) {
            $upp = new Upp(
                new HashMapPricingResolver(ArrayUtils::getNestedArrayValue('mixins', $priceConfig, [])),
                $client->getContainer()->get('translator')
            );

            $parsedConfig = $upp->parsePricingConfig($priceConfig['config'], $structureOptions);

            foreach($priceConfig['priceTests'] as $index => $pTest) {
                $contextData = $pTest['context'];
                $testAmounts = $pTest['tests'];

                $context = new PricingContext();
                $context->setCurrency($contextData['currency']);
                $context->setBookingDate($contextData['bookingDate']);
                $context->setArrivalDate($contextData['arrivalDate']);
                $context->setDepartureDate($contextData['departureDate']);
                $context->setGuests($contextData['guests']);

                $pricing = $upp->generatePrice($context, $parsedConfig);

                if (isset($testAmounts['noNights'])) {
                    $this->setName($this->getCurrentTestName($priceConfig, $index, 'noNights'));
                    $this->assertSame($testAmounts['noNights'], $pricing->getStay()->getNoNights());
                }

                if (isset($testAmounts['adjustmentCount'])) {
                    $this->setName($this->getCurrentTestName($priceConfig, $index, 'adjustmentCount'));
                    $this->assertSame($testAmounts['adjustmentCount'], count($pricing->getAdjustments()));
                }

                if (isset($testAmounts['basePrice'])) {
                    $this->setName($this->getCurrentTestName($priceConfig, $index, 'basePrice'));
                    $this->assertSame($testAmounts['basePrice'], (int) $pricing->getBasePrice()->getAmount());
                }

                if (isset($testAmounts['finalPrice'])) {
                    $this->setName($this->getCurrentTestName($priceConfig, $index, 'finalPrice'));
                    $this->assertSame($testAmounts['finalPrice'], (int)$pricing->getTotal()->getAmount());
                }

                if (isset($testAmounts['basePrice'])) {
                    $this->setName($this->getCurrentTestName($priceConfig, $index, 'basePrice'));
                    $this->assertSame($testAmounts['basePrice'], (int) $pricing->getBasePrice()->getAmount());
                }

                if (isset($testAmounts['damageDeposit'])) {
                    $this->setName($this->getCurrentTestName($priceConfig, $index, 'damageDeposit'));
                    $this->assertSame($testAmounts['damageDeposit'], (int) $pricing->getDamageDeposit()->getAmount());
                }

                if (isset($testAmounts['split'])) {
                    $this->setName($this->getCurrentTestName($priceConfig, $index, 'split.deposit'));
                    $this->assertSame($testAmounts['split']['deposit'], (int) $pricing->getSplitDetails()->getDeposit()->getAmount());
                    $this->setName($this->getCurrentTestName($priceConfig, $index, 'split.balance'));
                    $this->assertSame($testAmounts['split']['balance'], (int) $pricing->getSplitDetails()->getBalance()->getAmount());
                }

                if (isset($testAmounts['nightPrices'])) {
                    foreach ($pricing->getStay()->getNights() as $date => $day) {
                        $this->setName($this->getCurrentTestName($priceConfig, $index, 'nightPrices: '. $date));
                        $this->assertSame($testAmounts['nightPrices'][$date], (int) $day->getCost()->getAmount());
                    }
                }
            }
        }


    }

}