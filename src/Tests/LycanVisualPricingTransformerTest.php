<?php

namespace Tests;

use Aptenex\Upp\Exception\InvalidPricingConfigException;
use Aptenex\Upp\Parser\Resolver\HashMapPricingResolver;
use Aptenex\Upp\Parser\Structure\StructureOptions;
use Aptenex\Upp\Transformer\LycanVisualPricingTransformer;
use Aptenex\Upp\Upp;
use Aptenex\Upp\Util\TestUtils;
use PHPUnit\Framework\TestCase;
use Translation\TestTranslator;

class LycanVisualPricingTransformerTest extends TestCase
{

    public const VISUAL_PRICE_TEST_CONFIG_NIGHTLY = 'lycan-visual-transformer-test-nightly';
    public const VISUAL_PRICE_TEST_CONFIG_WEEKLY = 'lycan-visual-transformer-test-weekly';

    public function testNightlyLowAndHighWorksAsExpected()
    {
        $distributionConfig = TestUtils::getPriceTestByKey(
            json_decode(file_get_contents(__DIR__ . '/Resources/test-configs.json'), true),
            self::VISUAL_PRICE_TEST_CONFIG_NIGHTLY
        )['config'];

        $upp = new Upp(
            new HashMapPricingResolver([]),
            new TestTranslator()
        );

        // no channel set so keep it - this is because it is only being parsed and for realtime stuff,
        // the pricing context channel will handle this anyway
        $structureOptions = new StructureOptions();

        $parsed = $upp->parsePricingConfig($distributionConfig, $structureOptions);

        $lvpt = new LycanVisualPricingTransformer(true);

        $actualVisual = $lvpt->transform($parsed);

        $expectedVisual = [
            'currency' => 'GBP',
            'nightlyLow' => 35,
            'nightlyHigh' => 500,
            'weeklyLow' => 245,
            'weeklyHigh' => 3500
        ];

        $this->assertSame($expectedVisual, $actualVisual);
    }

    public function testWeeklyLowAndHighWorksAsExpected()
    {
        $distributionConfig = TestUtils::getPriceTestByKey(
            json_decode(file_get_contents(__DIR__ . '/Resources/test-configs.json'), true),
            self::VISUAL_PRICE_TEST_CONFIG_WEEKLY
        )['config'];

        $upp = new Upp(
            new HashMapPricingResolver([]),
            new TestTranslator()
        );

        // no channel set so keep it - this is because it is only being parsed and for realtime stuff,
        // the pricing context channel will handle this anyway
        $structureOptions = new StructureOptions();

        $parsed = $upp->parsePricingConfig($distributionConfig, $structureOptions);

        $lvpt = new LycanVisualPricingTransformer(true);

        $actualVisual = $lvpt->transform($parsed);

        $expectedVisual = [
            'currency' => 'GBP',
            'nightlyLow' => 71,
            'nightlyHigh' => 86,
            'weeklyLow' => 497,
            'weeklyHigh' => 602
        ];

        $this->assertSame($expectedVisual, $actualVisual);
    }

}