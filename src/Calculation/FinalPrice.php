<?php

namespace Aptenex\Upp\Calculation;

use Money\Money;
use Aptenex\Upp\Util\MoneyUtils;
use Aptenex\Upp\Helper\LanguageTools;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Parser\Structure\Period;
use Aptenex\Upp\Parser\Structure\PricingConfig;
use Aptenex\Upp\Exception\InvalidPricingConfigException;
use Aptenex\Upp\Calculation\SplitAmount\GuestSplitOverview;

class FinalPrice
{

    /**
     * @var Money
     */
    private $total;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var Money
     */
    private $basePrice;

    /**
     * @var Money
     */
    private $damageDeposit;

    /**
     * @var GuestSplitOverview
     */
    private $splitDetails;

    /**
     * @var AdjustmentAmount[]
     */
    private $adjustments = [];

    /**
     * @var Stay
     */
    private $stay;

    /**
     * @var PricingContext
     */
    private $contextUsed;

    /**
     * @var PricingConfig
     */
    private $configUsed;

    /**
     * @var string
     */
    private $bookableType = Period::BOOKABLE_TYPE_DEFAULT;

    /**
     * FinalPrice constructor.
     * @param PricingContext $contextUsed
     * @param PricingConfig $configUsed
     */
    public function __construct(PricingContext $contextUsed, PricingConfig $configUsed)
    {
        $this->currency = strtoupper(trim($contextUsed->getCurrency()));
        $this->contextUsed = $contextUsed;
        $this->configUsed = $configUsed;
        $this->stay = new Stay($contextUsed);
        $this->total = \Aptenex\Upp\Util\MoneyUtils::newMoney(0, $this->getCurrency());
        $this->basePrice = \Aptenex\Upp\Util\MoneyUtils::newMoney(0, $this->getCurrency());
        $this->damageDeposit = \Aptenex\Upp\Util\MoneyUtils::newMoney(0, $this->getCurrency());
        $this->splitDetails = new GuestSplitOverview();

        $this->validateCurrency();
    }

    /**
     * @throws InvalidPricingConfigException
     */
    private function validateCurrency()
    {
        if (!array_key_exists($this->getCurrency(), $this->getConfigUsed()->getCurrencyConfigs())) {
            throw new InvalidPricingConfigException(LanguageTools::trans('CURRENCY_NOT_CONFIGURED'));
        }
    }

    /**
     * @return \Aptenex\Upp\Parser\Structure\CurrencyConfig
     */
    public function getCurrencyConfigUsed()
    {
        return $this->getConfigUsed()->getCurrencyConfigs()[$this->getCurrency()];
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return Money
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param Money $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return Money
     */
    public function getBasePrice()
    {
        return $this->basePrice;
    }

    /**
     * @param Money $basePrice
     */
    public function setBasePrice($basePrice)
    {
        $this->basePrice = $basePrice;
    }

    /**
     * @return Money
     */
    public function getDamageDeposit()
    {
        return $this->damageDeposit;
    }

    /**
     * @param Money $damageDeposit
     */
    public function setDamageDeposit($damageDeposit)
    {
        $this->damageDeposit = $damageDeposit;
    }

    /**
     * @return AdjustmentAmount[]
     */
    public function getAdjustments()
    {
        return $this->adjustments;
    }

    /**
     * @param AdjustmentAmount[] $adjustments
     */
    public function setAdjustments($adjustments)
    {
        $this->adjustments = $adjustments;
    }

    /**
     * @param $adjustmentType
     *
     * @return bool
     */
    public function hasAdjustmentByType($adjustmentType)
    {
        foreach($this->adjustments as $adj) {
            if ($adj->getType() === $adjustmentType) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param AdjustmentAmount $adjustment
     */
    public function addAdjustment(AdjustmentAmount $adjustment)
    {
        $this->adjustments[] = $adjustment;
    }

    /**
     * @return PricingContext
     */
    public function getContextUsed()
    {
        return $this->contextUsed;
    }

    /**
     * @return PricingConfig
     */
    public function getConfigUsed()
    {
        return $this->configUsed;
    }

    /**
     * @return Stay
     */
    public function getStay()
    {
        return $this->stay;
    }

    /**
     * @return string
     */
    public function getBookableType()
    {
        return $this->bookableType;
    }

    /**
     * @param string $bookableType
     */
    public function setBookableType($bookableType)
    {
        $this->bookableType = $bookableType;
    }

    /**
     * @return GuestSplitOverview
     */
    public function getSplitDetails()
    {
        return $this->splitDetails;
    }

    public function disableSplitDetails()
    {
        $this->splitDetails = null;
    }

    /**
     * @return array
     */
    private function getAdjustmentsArray()
    {
        $d = [];

        foreach($this->getAdjustments() as $a) {
            $d[] = $a->__toArray();
        }

        return $d;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return [
            'currency'      => $this->getCurrency(),
            'description'   => !empty($this->getContextUsed()->getDescription()) ? $this->getContextUsed()->getDescription() : null,
            'total'         => MoneyUtils::getConvertedAmount($this->getTotal()),
            'basePrice'     => MoneyUtils::getConvertedAmount($this->getBasePrice()),
            'damageDeposit' => MoneyUtils::getConvertedAmount($this->getDamageDeposit()),
            'bookableType'  => $this->getBookableType(),
            'adjustments'   => $this->getAdjustmentsArray(),
            'stayBreakdown' => $this->getStay()->__toArray(),
            'splitDetails'  => !is_null($this->splitDetails) ? $this->splitDetails->__toArray() : null
        ];
    }

}