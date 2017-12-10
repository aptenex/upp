<?php

namespace Aptenex\Upp\Calculation\Pricing;

use Aptenex\Upp\Calculation\AdjustmentAmount;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Parser\Structure\SplitMethod;
use Money\Money;

class ExtraAmountCalculator
{

    public function calculateAndApplyAdjustments(FinalPrice $fp)
    {

        /*
         * For now we will simply transform these extras into adjustments
         *
         * All extras will be an addition adjustment
         */

        foreach($fp->getStay()->getExtras() as $extra) {
            $adjustment = new AdjustmentAmount(
                \Aptenex\Upp\Util\MoneyUtils::fromString($extra->getAmount(), $fp->getCurrency()),
                strtolower(trim($extra->getName())),
                vsprintf("%s%s%s%s", [
                    $extra->getName(),
                    !empty($extra->getDescription()) ? ' (' : '',
                    $extra->getDescription(),
                    !empty($extra->getDescription()) ? '(' : ''
                ]),
                Operand::OP_ADDITION,
                AdjustmentAmount::TYPE_EXTRA,
                AdjustmentAmount::PRICE_GROUP_TOTAL,
                SplitMethod::ON_TOTAL,
                false // Extras are not hidden
            );

            $fp->addAdjustment($adjustment);
        }
    }

}