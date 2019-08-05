<?php

namespace Tests;

use Aptenex\Upp\Parser\Structure\Modifier;
use Aptenex\Upp\Upp;
use Aptenex\Upp\Util\ConfigUtils;
use Aptenex\Upp\Util\TestUtils;
use Translation\TestTranslator;
use PHPUnit\Framework\TestCase;
use Aptenex\Upp\Exception\InvalidPricingConfigException;
use Aptenex\Upp\Parser\Resolver\HashMapPricingResolver;
use Aptenex\Upp\Parser\Structure\StructureOptions;

class DistributionParserTest extends TestCase
{

    public const PARSED_CONFIG_DISTRIBUTION_KEY = 'distribution-parser-test';

    public function testParsedConfigDistributionExcludedDefaultNoChannelProvided(): void
    {
        $distributionConfig = TestUtils::getPriceTestByKey(
            json_decode(file_get_contents(__DIR__ . '/Resources/test-configs.json'), true),
            self::PARSED_CONFIG_DISTRIBUTION_KEY
        )['config'];

        $upp = new Upp(
            new HashMapPricingResolver([]),
            new TestTranslator()
        );

        // no channel set so keep it - this is because it is only being parsed and for realtime stuff,
        // the pricing context channel will handle this anyway
        $structureOptions = new StructureOptions();

        try {
            $parsed = $upp->parsePricingConfig($distributionConfig, $structureOptions);

            foreach($parsed->getCurrencyConfigs() as $cc) {
                $this->assertCount(2,  $cc->getModifiers());
            }

        } catch (InvalidPricingConfigException $e) {
            $this->assertFalse(true, $e->getMessage());
        }
    }

    public function testParsedConfigDistributionExcluded(): void
    {
        $distributionConfig = TestUtils::getPriceTestByKey(
            json_decode(file_get_contents(__DIR__ . '/Resources/test-configs.json'), true),
            self::PARSED_CONFIG_DISTRIBUTION_KEY
        )['config'];

        $upp = new Upp(
            new HashMapPricingResolver([]),
            new TestTranslator()
        );

        // Don't set a channel, so the modifier should be removed
        $structureOptions = new StructureOptions();
        $structureOptions->setDistributionChannel('rentivo'); // This test has "homeaway"

        try {
            $parsed = $upp->parsePricingConfig($distributionConfig, $structureOptions);

            foreach($parsed->getCurrencyConfigs() as $cc) {
                $this->assertCount(1,  $cc->getModifiers());
            }

        } catch (InvalidPricingConfigException $e) {
            $this->assertFalse(true, $e->getMessage());
        }
    }

    public function testParsedConfigDistributionIncludedConditionRemoved(): void
    {
        $distributionConfig = TestUtils::getPriceTestByKey(
            json_decode(file_get_contents(__DIR__ . '/Resources/test-configs.json'), true),
            self::PARSED_CONFIG_DISTRIBUTION_KEY
        )['config'];

        $upp = new Upp(
            new HashMapPricingResolver([]),
            new TestTranslator()
        );

        $structureOptions = new StructureOptions();
        $structureOptions->setDistributionChannel('homeaway'); // This test has "homeaway"

        try {
            $parsed = $upp->parsePricingConfig($distributionConfig, $structureOptions);

            $checkedModifierCon = false;
            foreach($parsed->getCurrencyConfigs() as $cc) {
                // cleaning fee & cc fee
                $this->assertCount(2,  $cc->getModifiers());

                foreach($cc->getModifiers() as $modifier) {
                    if ($modifier->getDescription() === 'CC Fee') {
                        // Assert that we still have the nights condition
                        $this->assertCount(1, $modifier->getConditions());#
                        $checkedModifierCon = true;
                    }
                }
            }

            $this->assertTrue($checkedModifierCon);

        } catch (InvalidPricingConfigException $e) {
            $this->assertFalse(true, $e->getMessage());
        }
    }

    public function testUnParsedConfigDistributionNoChannelDefault(): void
    {
        $distributionConfig = TestUtils::getPriceTestByKey(
            json_decode(file_get_contents(__DIR__ . '/Resources/test-configs.json'), true),
            self::PARSED_CONFIG_DISTRIBUTION_KEY
        )['config'];

        $config = ConfigUtils::filterDistributionConditions($distributionConfig, null);

        $modifiers = $config['data'][0]['modifiers'];

        $this->assertCount(1, $modifiers); // cleaning fee, excluded the dist. channel condition
    }

    public function testUnParsedConfigDistributionExcluded(): void
    {
        $distributionConfig = TestUtils::getPriceTestByKey(
            json_decode(file_get_contents(__DIR__ . '/Resources/test-configs.json'), true),
            self::PARSED_CONFIG_DISTRIBUTION_KEY
        )['config'];

        $config = ConfigUtils::filterDistributionConditions($distributionConfig, 'RANDOM_FALSE_CHANNEL');

        $modifiers = $config['data'][0]['modifiers'];

        $this->assertCount(1, $modifiers); // cleaning fee
    }

    public function testUnParsedConfigDistributionIncludedConditionRemoved(): void
    {
        $distributionConfig = TestUtils::getPriceTestByKey(
            json_decode(file_get_contents(__DIR__ . '/Resources/test-configs.json'), true),
            self::PARSED_CONFIG_DISTRIBUTION_KEY
        )['config'];

        $config = ConfigUtils::filterDistributionConditions($distributionConfig, 'homeaway');

        $modifiers = $config['data'][0]['modifiers'];

        $this->assertCount(2, $modifiers); // cleaning fee & dist modifier but with the condition removed

        $checked = false;
        foreach($modifiers as $mo) {
            if ($mo['description'] === 'CC Fee') {
                $checked = true;
                $this->assertCount(1, $mo['conditions']);
            }
        }

        $this->assertTrue($checked);
    }

}