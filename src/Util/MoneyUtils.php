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

    /**
     * @param $amount
     * @param $currency
     *
     * @return Money
     */
    public static function fromString($amount, $currency)
    {
        if (is_null(self::$moneyParser)) {
            self::$moneyParser = new DecimalMoneyParser(new ISOCurrencies());
        }

        if ($currency instanceof Currency) {
            $currency = $currency->getCode();
        }

        if (empty($amount) || is_null($amount)) {
            $amount = 0;
        }

        return self::$moneyParser->parse((string) $amount, strtoupper($currency));
    }

    /**
     * @param int $amount
     * @param string|Currency $currency
     *
     * @return Money
     */
    public static function newMoney($amount, $currency)
    {
        if (is_string($amount)) {
            $amount = (int) $amount;
        }

        if (!$currency instanceof \Money\Currency) {
            $currency = new Currency(strtoupper($currency));
        }

        return new Money($amount, $currency);
    }

    /**
     * @param Money $money
     *
     * @return float
     */
    public static function getConvertedAmount(Money $money)
    {
        if (is_null(self::$moneyFormatter)) {
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
    public static function formatMoney($money, $locale = 'en_GB')
    {
        $i = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        $f = new IntlMoneyFormatter($i, new ISOCurrencies());

        return $f->format($money);
    }

    /**
     * @param string $amount
     * @param string $currency
     * @param string $locale
     *
     * @return string
     */
    public static function formatMoneyRaw($amount, $currency, $locale)
    {
        if (is_null($amount) || empty($amount)) {
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
    public static function fromSmallestToNormalized($smallestCurrencyUnit, $currency)
    {
        $money = \Aptenex\Upp\Util\MoneyUtils::newMoney($smallestCurrencyUnit, new Currency(strtoupper($currency)));

        return self::getConvertedAmount($money);
    }

    /**
     * @param $amount
     * @param $locale
     *
     * @return string
     */
    public static function formatNumber($amount, $locale)
    {
        return number_format($amount, 2);
    }

}