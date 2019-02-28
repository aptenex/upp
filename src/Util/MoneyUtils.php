<?php

namespace Aptenex\Upp\Util;

use Money\Money;
use Money\Currency;
use Money\Currencies\ISOCurrencies;
use Money\Parser\DecimalMoneyParser;
use Money\Formatter\IntlMoneyFormatter;
use Money\Formatter\DecimalMoneyFormatter;

class MoneyUtils
{

    private static $moneyParser;
    private static $moneyFormatter;

    public static $currencyCache = ['__count' => 0];
    public static $moneyInstanceCacheFromString = ['__count' => 0];
    public static $moneyInstanceCacheNewMoney = ['__count' => 0];

    public static function getCurrency($currency)
    {
        if ($currency instanceof Currency) {
            return $currency;
        }

        if (isset(self::$currencyCache[$currency])) {
            self::$currencyCache['__count']++;
            return self::$currencyCache[$currency];
        }

        $currency = new Currency(strtoupper($currency));

        self::$currencyCache[$currency->getCode()] = $currency;

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
            self::$moneyParser = new DecimalMoneyParser(new ISOCurrencies());
        }
    
        $currency = self::getCurrency($currency);

        if (empty($amount) || $amount === null) {
            $amount = 0;
        }

        $key = $currency->getCode() . '_' . $amount;

        if (isset(self::$moneyInstanceCacheFromString[$key])) {
            self::$moneyInstanceCacheFromString['__count']++;
            return self::$moneyInstanceCacheFromString[$key];
        }

        $parsed = self::$moneyParser->parse((string) $amount, $currency);

        self::$moneyInstanceCacheFromString[$key] = $parsed;

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

        $key = $currency->getCode() . '_' . $amount;

        if (isset(self::$moneyInstanceCacheNewMoney[$key])) {
            self::$moneyInstanceCacheNewMoney['__count']++;
            return self::$moneyInstanceCacheNewMoney[$key];
        }

        $money = new Money($amount, $currency);

        self::$moneyInstanceCacheNewMoney[$key] = $money;

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
            self::$moneyFormatter = new DecimalMoneyFormatter(new ISOCurrencies());
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
        $f = new IntlMoneyFormatter($i, new ISOCurrencies());

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