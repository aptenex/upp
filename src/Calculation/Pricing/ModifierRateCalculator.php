<?php

namespace Aptenex\Upp\Calculation\Pricing;

use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Util\MoneyUtils;
use Aptenex\Upp\Helper\MoneyTools;
use Aptenex\Upp\Calculation\FinalPrice;

class ModifierRateCalculator
{

    /**
     * @param PricingContext $context
     * @param FinalPrice $fp
     */
    public function compute(PricingContext $context, FinalPrice $fp)
    {
        foreach($fp->getStay()->getModifiersUsed() as $modifier) {
            if (false) {
                $rateConfig = $modifier->getControlItemConfig()->getRate();

                $daysMatched = count($modifier->getMatchedNights());

                if ($rateConfig->getAmount() === 0 || $daysMatched === 0) {
                    continue;
                }

                if ($rateConfig->getType() === \Aptenex\Upp\Parser\Structure\Rate::TYPE_ADJUSTMENT) {

                    foreach ($modifier->getMatchedNights() as $key => $day) {
                        if ($rateConfig->getCalculationMethod() === \Aptenex\Upp\Parser\Structure\Rate::METHOD_PERCENTAGE) {
                            $value = $day->getCost()->multiply($rateConfig->getAmount());
                        } else {
                            $value = MoneyUtils::fromString($rateConfig->getAmount(), $fp->getCurrency());
                        }

                        $day->setCost(MoneyTools::applyMonetaryOperand(
                            $day->getCost(),
                            $value,
                            $rateConfig->getCalculationOperand()
                        ));
                    }

                }
            } else {
                $cuc = new RatePerConditionalUnitCalculator();
                $cuc->applyConditionalRateModifications($fp, $modifier);
            }
        }
    }

}