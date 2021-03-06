<?php

namespace Aptenex\Upp\Util;

use Aptenex\Upp\Money\RentivoCurrencies;
use Money\Money;
use Money\Currency;
use Money\Parser\DecimalMoneyParser;
use Money\Formatter\IntlMoneyFormatter;
use Money\Formatter\DecimalMoneyFormatter;

class MoneyUtils
{

    private static $moneyParser;
    private static $moneyFormatter;

    /**
     * @param $percentage
     *
     * @return float
     */
    public static function normalizePercentage($percentage): float
    {
        // Anything above or equal to 2 is considered someone entering the % in as 0 - 100 instead of a decimal
        if ($percentage >= 2) {
            return $percentage / 100;
        }

        return (float) $percentage;
    }

    public static function getCurrency($currency)
    {
        if ($currency instanceof Currency) {
            return $currency;
        }

        $currency = new Currency(strtoupper($currency));

        return $currency;
    }

    /**
     * @param $amount
     * @param $currency
     *
     * @return Money
     */
    public static function fromString($amount, $currency): Money
    {
        if (self::$moneyParser === null) {
            self::$moneyParser = new DecimalMoneyParser(new RentivoCurrencies());
        }
    
        $currency = self::getCurrency($currency);

        if (empty($amount) || $amount === null) {
            $amount = 0;
        }

        $parsed = self::$moneyParser->parse((string) $amount, $currency);

        return $parsed;
    }

    /**
     * @param int|string $amount
     * @param string|Currency $currency
     *
     * @return Money
     */
    public static function newMoney($amount, $currency): Money
    {
        if (\is_string($amount)) {
            $amount = (int) $amount;
        }

        $currency = self::getCurrency($currency);

        $money = new Money($amount, $currency);

        return $money;
    }

    /**
     * @param Money $money
     *
     * @return float
     */
    public static function getConvertedAmount(Money $money): float
    {
        if (self::$moneyFormatter === null) {
            self::$moneyFormatter = new DecimalMoneyFormatter(new RentivoCurrencies());
        }

        return (float) self::$moneyFormatter->format($money);
    }

    /**
     * @param Money  $money
     * @param string $locale
     *
     * @return string
     */
    public static function formatMoney($money, $locale = 'en_GB'): string
    {
        $i = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        $f = new IntlMoneyFormatter($i, new RentivoCurrencies());

        return $f->format($money);
    }

    /**
     * @param string|int $amount
     * @param string $currency
     * @param string $locale
     *
     * @return string
     */
    public static function formatMoneyRaw($amount, $currency, $locale): string
    {
        if ($amount === null || empty($amount)) {
            $amount = 0;
        }

        $money = self::fromString($amount, $currency);

        return self::formatMoney($money, $locale);
    }

    /**
     * @param int $smallestCurrencyUnit
     * @param string $currency
     *
     * @return float
     */
    public static function fromSmallestToNormalized($smallestCurrencyUnit, $currency): float
    {
        $money = self::newMoney($smallestCurrencyUnit, new Currency(strtoupper($currency)));

        return self::getConvertedAmount($money);
    }

    /**
     * @param $amount
     * @param $locale
     *
     * @return string
     */
    public static function formatNumber($amount, $locale): string
    {
        return number_format($amount, 2);
    }

}