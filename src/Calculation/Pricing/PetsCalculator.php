<?php

namespace Aptenex\Upp\Calculation\Pricing;

use Aptenex\Upp\Util\MoneyUtils;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Calculation\AdjustmentAmount;

class PetsCalculator
{

    public function calculateAndApplyAdjustments(FinalPrice $fp)
    {
        $context = $fp->getContextUsed();

        if (!$context->hasPets()) {
            return;
        }

        $defaults = $fp->getCurrencyConfigUsed()->getDefaults();

        if (!$defaults->hasPerPetPerStay() && !$defaults->hasPerPetPerNight()) {
            return;
        }

        $totalPetAmount = MoneyUtils::fromString(0, $fp->getCurrency());

        if ($defaults->hasPerPetPerStay()) {
            $perPerPerStayAmount = MoneyUtils::fromString($defaults->getPerPetPerStay(), $fp->getCurrency());
            $perPetPerStayTotal = $perPerPerStayAmount->multiply($context->getPets());

            $totalPetAmount = $totalPetAmount->add($perPetPerStayTotal);
        }

        if ($defaults->hasPerPetPerNight()) {
            $perPerPerNightAmount = MoneyUtils::fromString($defaults->getPerPetPerNight(), $fp->getCurrency());
            $perPetPerNightTotal = $perPerPerNightAmount->multiply($fp->getStay()->getNoNights())->multiply($context->getPets());

            $totalPetAmount = $totalPetAmount->add($perPetPerNightTotal);
        }

        if (!$totalPetAmount->isPositive() || $totalPetAmount->isZero()) {
            return; // Must be above 0
        }

        $adjustment = new AdjustmentAmount(
            $totalPetAmount,
            'pet_fee',
            vsprintf('%sx Pet Fee', [
                $context->getPets()
            ]),
            Operand::OP_ADDITION,
            AdjustmentAmount::TYPE_EXTRA,
            AdjustmentAmount::PRICE_GROUP_TOTAL,
            $defaults->getPerPetSplitMethod(),
            false // Extras are not hidden
        );

        $fp->addAdjustment($adjustment);
    }

}