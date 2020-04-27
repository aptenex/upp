<?php

namespace Aptenex\Upp\Money;

use Money\Currencies;
use Money\Currency;
use Money\Exception\UnknownCurrencyException;

/***
 * Class RentivoCurrencies
 *
 * moneyphp/money have decided that nearly all their classes will be final including currency related classes.
 *
 * This means making small changes to various currencies such as the Indonesian Rupiah (IDR) becomes impossible, the
 * use case for this being IDR's inflation renders their minor unit (officially 2) as useless. In the real world
 * they do not use the minor units so payments etc need to reflect the actual reality as well.
 *
 * This class wraps the ISOCurrency class (which is final!!!) and detects when IDR is being loaded and makes the
 * necessary replacements.
 *
 * @package Aptenex\Upp\Money
 */

class RentivoCurrencies implements Currencies
{

    /**
     * @var Currencies\ISOCurrencies
     */
    private $isoCurrencies;

    /**
     * @var array
     */
    private $overloadCurrencies = [
        'IDR' => [
            'alphabeticCode' => 'IDR',
            'currency' => 'Rupiah',
            'minorUnit' => 0,
            'numericCode' => 360,
        ]
    ];

    public function __construct()
    {
        $this->isoCurrencies = new Currencies\ISOCurrencies();
    }

    public function overloadContains(Currency $currency): bool
    {
        return isset($this->overloadCurrencies[$currency->getCode()]);
    }

    /**
     * {@inheritdoc}
     */
    public function contains(Currency $currency)
    {
        if ($this->overloadContains($currency)) {
            return true;
        }

        return $this->isoCurrencies->contains($currency);
    }

    /**
     * {@inheritdoc}
     */
    public function subunitFor(Currency $currency)
    {
        if ($this->overloadContains($currency)) {
            return $this->overloadCurrencies[$currency->getCode()]['minorUnit'];
        }

        return $this->isoCurrencies->subunitFor($currency);
    }

    /**
     * Returns the numeric code for a currency.
     *
     * @param Currency $currency
     *
     * @return int
     *
     * @throws UnknownCurrencyException If currency is not available in the current context
     */
    public function numericCodeFor(Currency $currency)
    {
        if ($this->overloadContains($currency)) {
            return $this->overloadCurrencies[$currency->getCode()]['numericCode'];
        }

       return $this->isoCurrencies->numericCodeFor($currency);
    }

    /**
     * @return \Traversable
     */
    public function getIterator()
    {
        return $this->isoCurrencies->getIterator();
    }

}