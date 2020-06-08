<?php

namespace Aptenex\Upp\Parser\ExternalConfig;

use Aptenex\Upp\Parser\Structure\Condition;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Parser\Structure\Period;
use Aptenex\Upp\Parser\Structure\PricingConfig;
use Aptenex\Upp\Parser\Structure\Rate;

class NightlyTariffOverrideCommand implements ExternalCommandInterface
{

    /**
     * @var float
     */
    private $nightlyTariff;

    /**
     * @var bool
     */
    private $removeDiscountModifiers;

    /**
     * @param float $nightlyTariff
     * @param bool $removeDiscountModifiers
     */
    public function __construct($nightlyTariff, $removeDiscountModifiers = true)
    {
        $this->nightlyTariff = $nightlyTariff;
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
        $newRate->setAmount($this->nightlyTariff);
        $newRate->setType(Rate::TYPE_NIGHTLY);
        $newRate->setCalculationMethod(Rate::METHOD_FIXED);
        $newRate->setCalculationOperand(Operand::OP_EQUALS);

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