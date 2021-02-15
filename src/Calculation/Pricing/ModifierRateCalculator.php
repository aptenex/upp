<?php

namespace Aptenex\Upp\Calculation\Pricing;

use Aptenex\Upp\Calculation\AdjustmentAmount;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Helper\LanguageTools;
use Aptenex\Upp\Parser\Structure\Modifier;
use Aptenex\Upp\Util\MoneyUtils;
use function in_array;

class ModifierRateCalculator
{

    /**
     * @param PricingContext $context
     * @param FinalPrice $fp
     * @param array $calculationOrders
     * @param array $priceGroups
     */
    public function compute(PricingContext $context, FinalPrice $fp, array $calculationOrders = [], array $priceGroups = []): void
    {
        foreach($fp->getStay()->getModifiersUsed() as $modifier) {

            /** @var Modifier $modConfig */
            $modConfig = $modifier->getControlItemConfig();

            if (!empty($priceGroups) && !in_array($modConfig->getPriceGroup(), $priceGroups, true)) {
                continue;
            }

            /** @var Modifier $modifierConfig */
            $modifierConfig = $modifier->getControlItemConfig();

            if (
                !empty($calculationOrders) &&
                !in_array($modifierConfig->getCalculationOrderFromType(), $calculationOrders, true)
            ) {
                continue;
            }

            if ($modConfig->supportsConditionalPerUnitRates()) {
                $cuc = new RatePerConditionalUnitCalculator();
                $cuc->applyConditionalRateModifications($fp, $modifier);
            } else {

                $guests = $fp->getContextUsed()->getGuests();

                if ($guests <= $modConfig->getRate()->getApplyOverMinimumGuests()) {
                    continue; // skip
                }

                $guests = $guests - $modConfig->getRate()->getApplyOverMinimumGuests();

                $amount = MoneyUtils::fromString($modConfig->getRate()->getAmount(), $fp->getCurrency());

                switch ($modConfig->getRate()->getCalculationMethod()) {

                    case \Aptenex\Upp\Parser\Structure\Rate::METHOD_FLAT_PER_GUEST:

                        $adjustmentAmount = $amount->multiply($guests);

                        $description = vsprintf('%s (%sx %s)', [
                            $modConfig->getDescription(),
                            $guests,
                            LanguageTools::transChoice('GUEST_UNIT', $guests)
                        ]);

                        break;

                    case \Aptenex\Upp\Parser\Structure\Rate::METHOD_FLAT_PER_NIGHT:

                        $adjustmentAmount = $amount->multiply($fp->getContextUsed()->getNoNights());

                        $description = vsprintf('%s (%sx %s)', [
                            $modConfig->getDescription(),
                            $fp->getContextUsed()->getNoNights(),
                            LanguageTools::transChoice('NIGHT_UNIT', $fp->getContextUsed()->getNoNights())
                        ]);

                        break;

                    case \Aptenex\Upp\Parser\Structure\Rate::METHOD_FLAT_PER_GUEST_PER_NIGHT:

                        $adjustmentAmount = $amount
                            ->multiply($guests)
                            ->multiply($fp->getContextUsed()->getNoNights())
                        ;

                        $description = vsprintf('%s (%sx %s, %sx %s)', [
                            $modConfig->getDescription(),
                            $guests,
                            LanguageTools::transChoice('GUEST_UNIT', $guests),
                            $fp->getContextUsed()->getNoNights(),
                            LanguageTools::transChoice('NIGHT_UNIT', $fp->getContextUsed()->getNoNights())
                        ]);

                        break;

                }

                $fp->addAdjustment(new AdjustmentAmount(
                    $adjustmentAmount,
                    strtoupper(trim(str_replace(' ', '_', $description))),
                    $description,
                    $modConfig->getRate()->getCalculationOperand(),
                    AdjustmentAmount::TYPE_MODIFIER,
                    $modConfig->getPriceGroup(),
                    $modConfig->getSplitMethod(),
                    $modConfig->isHidden(),
                    $modifier
                ));

            }
        }
    }

}