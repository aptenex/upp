<?php

namespace Aptenex\Upp\Calculation\Pricing;

use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Helper\MoneyTools;

class ModifierRateCalculator
{

    /**
     * @param FinalPrice $fp
     */
    public function compute(FinalPrice $fp)
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
                            $value = \App\Util\MoneyUtils::fromString($rateConfig->getAmount(), $fp->getCurrency());
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