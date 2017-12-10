<?php

namespace Aptenex\Upp\Calculation\Pricing;

use Aptenex\Upp\Calculation\AdjustmentAmount;
use Aptenex\Upp\Calculation\ControlItem\Modifier;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Helper\MoneyTools;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Parser\Structure\Rate;
use Aptenex\Upp\Parser\Structure\SplitMethod;
use Money\Money;

class TaxesCalculator
{

    public function calculateAndApplyAdjustments(FinalPrice $fp)
    {

        /*
         * Taxes are very simple and we will use the parsed 'tax' directly
         *
         * They are applied after all calculations to certain objects - here we will
         * also create the Pricing Tax object
         */

        foreach($fp->getCurrencyConfigUsed()->getTaxes() as $tax) {

            if ($tax->getCalculationMethod() === Rate::METHOD_FIXED) {
                $adjustment = new AdjustmentAmount(
                    \Aptenex\Upp\Util\MoneyUtils::fromString($tax->getAmount(), $fp->getCurrency()),
                    strtolower(trim($tax->getName())),
                    sprintf("%s%s", $tax->getName(), !empty($tax->getDescription()) ? ' (' . $tax->getDescription() . ')' : ''),
                    Operand::OP_ADDITION,
                    AdjustmentAmount::TYPE_TAX,
                    AdjustmentAmount::PRICE_GROUP_TOTAL,
                    SplitMethod::ON_TOTAL,
                    false // Taxes are not hidden
                );

                $fp->addAdjustment($adjustment);
            } else if ($tax->getCalculationMethod() === Rate::METHOD_PERCENTAGE) {
                $calculableAmount = \Aptenex\Upp\Util\MoneyUtils::newMoney(0, $fp->getCurrency());

                if ($tax->isIncludeBasePrice()) {
                    $calculableAmount = $calculableAmount->add($fp->getBasePrice());
                }

                // All extras are done
                if ($tax->isIncludeExtras()) {
                    foreach($fp->getAdjustments() as $adjustment) {
                        if ($adjustment->getType() !== AdjustmentAmount::TYPE_EXTRA) {
                            continue;
                        }

                        if (empty($tax->getExtrasWhitelist()) || in_array($adjustment->getIdentifier(), $tax->getExtrasWhitelist(), true)) {
                            $calculableAmount = MoneyTools::applyMonetaryOperand(
                                $calculableAmount,
                                $adjustment->getAmount(),
                                $adjustment->getOperand()
                            );
                        }
                    }
                }

                // Now we need to see if any modifiers need this tax applying to them
                if (!empty($tax->getUuid())) {
                    foreach($fp->getAdjustments() as $adjustment) {
                        if (
                            $adjustment->getOperand() !== Operand::OP_ADDITION ||
                            $adjustment->getType() !== AdjustmentAmount::TYPE_MODIFIER ||
                            !$adjustment->hasControlItem()
                        ) {
                            continue;
                        }

                        /** @var Modifier $modifier */
                        $modifier = $adjustment->getControlItem();
                        $config = $modifier->getControlItemConfig();

                        if (!$config->getRate()->isTaxable()) {
                            continue;
                        }

                        if (empty($config->getRate()->getApplicableTaxes())) {
                            // Apply to this modifier
                            $calculableAmount = $calculableAmount->add($adjustment->getAmount());
                        } else if (in_array($tax->getUuid(), $config->getRate()->getApplicableTaxes(), true)) {
                            // Need to see if this modifier matches the uuid of this tax
                            $calculableAmount = $calculableAmount->add($adjustment->getAmount());
                        }
                    }
                }

                if ($calculableAmount->getAmount() === 0) {
                    continue;
                }


                $calculatedAmount = $calculableAmount->multiply($tax->getAmount()); // Amount is percentage

                $adjustment = new AdjustmentAmount(
                    $calculatedAmount,
                    strtolower(trim($tax->getName())),
                    sprintf("%s%s", $tax->getName(), !empty($tax->getDescription()) ? ' (' . $tax->getDescription() . ')' : ''),
                    Operand::OP_ADDITION,
                    AdjustmentAmount::TYPE_TAX,
                    AdjustmentAmount::PRICE_GROUP_TOTAL,
                    SplitMethod::ON_TOTAL,
                    false // Taxes are not hidden
                );

                $fp->addAdjustment($adjustment);
            }
        }
    }

}