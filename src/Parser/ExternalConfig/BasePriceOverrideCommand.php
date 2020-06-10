<?php

namespace Aptenex\Upp\Parser\ExternalConfig;

use Aptenex\Upp\Parser\Structure\Condition;
use Aptenex\Upp\Parser\Structure\ExtraNightsAlteration;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Parser\Structure\Period;
use Aptenex\Upp\Parser\Structure\PricingConfig;
use Aptenex\Upp\Parser\Structure\Rate;
use Aptenex\Upp\Parser\Structure\RateStrategy;

class BasePriceOverrideCommand implements ExternalCommandInterface
{

    /**
     * @var float
     */
    private $basePrice;

    /**
     * @var bool
     */
    private $removeDiscountModifiers;

    /**
     * @param float $basePrice
     * @param bool $removeDiscountModifiers
     */
    public function __construct($basePrice, $removeDiscountModifiers = true)
    {
        $this->basePrice = $basePrice;
        $this->removeDiscountModifiers = $removeDiscountModifiers;
    }

    /**
     * @param PricingConfig $config
     */
    public function apply(PricingConfig $config)
    {
        $dateCondition = new Condition\DateCondition();
        $dateCondition->setType(Condition::TYPE_DATE);
        $dateCondition->setStartDate('2010-01-01');
        $dateCondition->setEndDate('2100-12-31');

        $newPeriod = new Period();
        $newPeriod->setId(1);
        $newPeriod->setDescription('Nightly Tariff Override');
        $newPeriod->setMinimumNights(0);
        $newPeriod->setConditions([$dateCondition]);

        $newRate = new Rate();
        $newRate->setAmount($this->basePrice);
        $newRate->setType(Rate::TYPE_NIGHTLY);
        $newRate->setCalculationMethod(Rate::METHOD_FIXED);
        $newRate->setCalculationOperand(Operand::OP_EQUALS);

        $nightlyStrategy = new ExtraNightsAlteration();
        $nightlyStrategy->setApplyToTotal(true);
        $nightlyStrategy->setNightsMatchedOverridesPrice(true);
        $nightlyStrategy->setMakePreviousNightsSameRate(true);
        $nightlyStrategy->setBrackets([
            [
                'night' => '1+',
                'amount' => $this->basePrice
            ]
        ]);

        $rs = new RateStrategy();
        $rs->setExtraNightsAlteration($nightlyStrategy);
        $newRate->setStrategy($rs);

        $newPeriod->setRate($newRate);

        foreach ($config->getCurrencyConfigs() as $cConfig) {
            $cConfig->setPeriods([$newPeriod], false);

            if ($this->removeDiscountModifiers) {
                $newModifiers = [];

                foreach($cConfig->getModifiers() as $modifier) {
                    if ($modifier->isDiscount()) {
                        continue;
                    }

                    $newModifiers[] = $modifier;
                }

                $cConfig->setModifiers($newModifiers);
            }
        }
    }

}