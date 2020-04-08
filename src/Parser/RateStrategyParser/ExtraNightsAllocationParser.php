<?php

namespace Aptenex\Upp\Parser\RateStrategyParser;

use Aptenex\Upp\Calculation\Pricing\Strategy\BracketsEvaluator;
use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\BaseChildParser;
use Aptenex\Upp\Parser\Structure\ExtraNightsAlteration;
use Aptenex\Upp\Parser\Structure\Operator;
use Aptenex\Upp\Parser\Structure\PricingConfig;
use Aptenex\Upp\Parser\Structure\Rate;

class ExtraNightsAllocationParser extends BaseChildParser
{

    /**
     * @param array $data
     *
     * @return ExtraNightsAlteration
     */
    public function parse($data)
    {
        if (empty($data)) {
            return null; // No strategy set
        }

        $p = new ExtraNightsAlteration();

        $p->setApplyToTotal(ArrayAccess::get('applyToTotal', $data, false));
        $p->setMakePreviousNightsSameRate(ArrayAccess::get('makePreviousNightsSameRate', $data, true));
        $p->setEnablePerGuestPerNight(ArrayAccess::get('enablePerGuestPerNight', $data, false));
        $p->setNightsMatchedOverridesPrice(ArrayAccess::get('nightsMatchedOverridesPrice', $data, false));
        $p->setCalculationMethod(ArrayAccess::get('calculationMethod', $data, Rate::METHOD_FIXED));

        // Certain fields can't be enabled together as they don't make sense
        if ($p->isNightsMatchedOverridesPrice()) {
            $p->setApplyToTotal(false);
            $p->setMakePreviousNightsSameRate(false);

            // Cannot be percentage based when it is the final price
            $p->setCalculationMethod(Rate::METHOD_FIXED);
        } else if ($p->isMakePreviousNightsSameRate() === false) {
            $p->setApplyToTotal(false); // This requires make previous nights to be enabled
        }

        if (ArrayAccess::has('calculationOperator', $data)) {
            $p->setCalculationOperator(ArrayAccess::get('calculationOperator', $data, Operator::OP_EQUALS));
        } else {
            // Deprecated
            $p->setCalculationOperator(ArrayAccess::get('calculationOperand', $data, Operator::OP_EQUALS));
        }

        $p->setBrackets(ArrayAccess::get('brackets', $data, []));

        if (empty($p->getBrackets())) {
            // This strategy does not apply if there are no brackets
            return null;
        }

        if ($p->isEnablePerGuestPerNight()) {
            // we need to update this flag to show what is the lowest minimum for the guest count
            // as this will be used for los generation to determine when to increment the
            // occupancy count, getting an accurate figure for this will save a lot of pricing iterations

            // Check the brackets and see at what point does the guest count change
            $pmFlag = PricingConfig::FLAG_HAS_PER_GUEST_PERIOD_STRATEGY;

            // We only need to sample the minimum guests
            $expanded = (new BracketsEvaluator())->expandBracketsWithGuests($p->getBrackets(), 30, 20);

            $minimumGuests = null;
            foreach($expanded as $night => $guestMap) {
                if (\count($guestMap) === 1 && isset($guestMap['_default'])) {
                    continue; // Only default here no min value
                }

                unset($guestMap['_default']);

                $values = array_values($guestMap);
                // Pull the first value
                $firstGuestValue = array_shift($values);

                if ($firstGuestValue !== null) {
                    if ($minimumGuests === null) {
                        $minimumGuests = $firstGuestValue;
                    } else if ((int) $firstGuestValue < $minimumGuests) {
                        $minimumGuests = (int) $firstGuestValue;
                    }
                }
            }

            if ($minimumGuests !== null) {
                if (!$this->getConfig()->hasFlag($pmFlag)) {
                    $this->getConfig()->addFlag($pmFlag, $minimumGuests);
                } else {
                    // Flag has already been set once so we need to check and update if applicable
                    $currentMin = $this->getConfig()->getFlag($pmFlag);
                    if ($minimumGuests > $currentMin) {
                        $this->getConfig()->setFlag($pmFlag, $minimumGuests); // Update
                    }
                }
            }
        }

        return $p;
    }

}