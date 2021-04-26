<?php

namespace Aptenex\Upp\Tests;

use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Transformer\LycanVisualPricingTransformer;
use Aptenex\Upp\Upp;
use Aptenex\Upp\Translation\TestTranslator;
use PHPUnit\Framework\TestCase;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Parser\Structure\StructureOptions;
use Aptenex\Upp\Parser\Resolver\HashMapPricingResolver;

class MultipleCalculationTest extends TestCase
{

    public static $currentTestName = null;

    private function getCurrentTestName($priceConfig, $testIndex, $testKey, $prefix = '')
    {
        return vsprintf('testConfigs() %s // Name: %s %sTest Index: %s %sComparison: %s%s', [
            $prefix,
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
        $priceConfigs = json_decode(file_get_contents(__DIR__ . '/Resources/test-configs.json'), true);

        foreach($priceConfigs as $priceConfig) {
            if (isset($priceConfig['skip']) && $priceConfig['skip']) {
                continue;
            }

            $structureOptions = new StructureOptions();

            self::$currentTestName = $priceConfig['name'];

            if (!isset($priceConfig['key']) || $priceConfig['key'] !== 'wrs_partial_matched_discounts_apply') {
                continue;
            }

            $upp = new Upp(
                new HashMapPricingResolver(ArrayAccess::get('mixins', $priceConfig, [])),
                new TestTranslator()
            );

            if (isset($priceConfig['visualPriceTests'])) {
                $this->setName($this->getCurrentTestName($priceConfig, 'Visual Price Tests', 'n/a'));

                $vTests = $priceConfig['visualPriceTests'];

                $parsedVisual = $upp->parsePricingConfig($priceConfig['config'], null);

                $lvt = new LycanVisualPricingTransformer(true);

                $results = $lvt->transform($parsedVisual);

                if (isset($vTests['nightlyLow'])) {
                    $this->setName($this->getCurrentTestName($priceConfig, 0, 'nightlyLow'));
                    $this->assertSame($vTests['nightlyLow'], $results['nightlyLow']);
                }

                if (isset($vTests['nightlyHigh'])) {
                    $this->setName($this->getCurrentTestName($priceConfig, 0, 'nightlyHigh'));
                    $this->assertSame($vTests['nightlyHigh'], $results['nightlyHigh']);
                }

                if (isset($vTests['weeklyLow'])) {
                    $this->setName($this->getCurrentTestName($priceConfig, 0, 'weeklyLow'));
                    $this->assertSame($vTests['weeklyLow'], $results['weeklyLow']);
                }

                if (isset($vTests['weeklyHigh'])) {
                    $this->setName($this->getCurrentTestName($priceConfig, 0, 'weeklyHigh'));
                    $this->assertSame($vTests['weeklyHigh'], $results['weeklyHigh']);
                }
            }

            if (isset($priceConfig['priceTests'])) {
                foreach($priceConfig['priceTests'] as $index => $pTest) {

                    if (ArrayAccess::has('parseOptions', $pTest)) {
                        foreach($pTest['parseOptions'] as $key => $value) {
                            $setter = sprintf('set%s', ucfirst($key));
                            $structureOptions->$setter($value);
                        }
                    }

                    $parsedConfig = $upp->parsePricingConfig($priceConfig['config'], $structureOptions);

                    $this->executeTests(
                        $upp,
                        $pTest,
                        $priceConfig,
                        $index,
                        $parsedConfig
                    );

                    // We also want to test the __toArray() function on the parsed structure
                    // to make sure we can reverse it

                    $convertedConfig = $parsedConfig->__toArray();

                    $reParsedConfig = $upp->parsePricingConfig($convertedConfig, $structureOptions);

                    $this->executeTests(
                        $upp,
                        $pTest,
                        $priceConfig,
                        $index,
                        $reParsedConfig,
                        true
                    );

                }
            }
        }
    }

    private function executeTests(Upp $upp, array $pTest, $priceConfig, $index, $parsedConfig, $isReParsed = false)
    {
        $testNamePrefix = '';
        if ($isReParsed) {
            $testNamePrefix = 'RE-PARSED: ';
        }

        $contextData = $pTest['context'];
        $testAmounts = $pTest['tests'];

        $this->setName($this->getCurrentTestName($priceConfig, $index, 'n/a', $testNamePrefix));

        $context = new PricingContext();

        foreach($contextData as $key => $value) {
            $setter = sprintf('set%s', ucfirst($key));
            if (method_exists($context, $setter)) {
                $context->$setter($value);
            }
        }

        $pricing = $upp->generatePrice($context, $parsedConfig);

        if ($pricing->getErrors()->hasErrors()) {
            $this->setName($this->getCurrentTestName($priceConfig, $index, 'No Errors', $testNamePrefix));

            $this->assertFalse($pricing->getErrors()->hasErrors(), 'Pricing has errors');
        }

        if (isset($testAmounts['noNights'])) {
            $this->setName($this->getCurrentTestName($priceConfig, $index, 'noNights', $testNamePrefix));
            $this->assertSame($testAmounts['noNights'], $pricing->getStay()->getNoNights());
        }

        if (isset($testAmounts['adjustmentCount'])) {
            $this->setName($this->getCurrentTestName($priceConfig, $index, 'adjustmentCount', $testNamePrefix));
            $this->assertCount($testAmounts['adjustmentCount'], $pricing->getAdjustments());
        }

        if (isset($testAmounts['basePrice'])) {
            $this->setName($this->getCurrentTestName($priceConfig, $index, 'basePrice', $testNamePrefix));
            $this->assertSame($testAmounts['basePrice'], (int) $pricing->getBasePrice()->getAmount());
        }

        if (isset($testAmounts['finalPrice'])) {
            $this->setName($this->getCurrentTestName($priceConfig, $index, 'finalPrice', $testNamePrefix));
            $this->assertSame($testAmounts['finalPrice'], (int)$pricing->getTotal()->getAmount());
        }

        if (isset($testAmounts['basePrice'])) {
            $this->setName($this->getCurrentTestName($priceConfig, $index, 'basePrice', $testNamePrefix));
            $this->assertSame($testAmounts['basePrice'], (int) $pricing->getBasePrice()->getAmount());
        }

        if (isset($testAmounts['damageDeposit'])) {
            $this->setName($this->getCurrentTestName($priceConfig, $index, 'damageDeposit', $testNamePrefix));
            $this->assertSame($testAmounts['damageDeposit'], (int) $pricing->getDamageDeposit()->getAmount());
        }

        if (isset($testAmounts['split'])) {
            $this->setName($this->getCurrentTestName($priceConfig, $index, 'split.deposit', $testNamePrefix));
            $this->assertSame($testAmounts['split']['deposit'], (int) $pricing->getSplitDetails()->getDeposit()->getAmount());
            $this->setName($this->getCurrentTestName($priceConfig, $index, 'split.balance', $testNamePrefix));
            $this->assertSame($testAmounts['split']['balance'], (int) $pricing->getSplitDetails()->getBalance()->getAmount());
        }

        if (isset($testAmounts['nightPrices'])) {
            foreach ($pricing->getStay()->getNights() as $date => $day) {
                $this->setName($this->getCurrentTestName($priceConfig, $index, 'nightPrices: '. $date, $testNamePrefix));
                $this->assertSame($testAmounts['nightPrices'][$date], (int) $day->getCost()->getAmount());
            }
        }
    }

}