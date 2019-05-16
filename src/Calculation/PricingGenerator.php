<?php

namespace Aptenex\Upp\Calculation;

use Aptenex\Upp\Calculation\Condition\Evaluator;
use Aptenex\Upp\Calculation\ControlItem\Modifier;
use Aptenex\Upp\Calculation\ControlItem\Period;
use Aptenex\Upp\Calculation\Pricing\BasicRateCalculator;
use Aptenex\Upp\Calculation\Pricing\DamageDepositCalculator;
use Aptenex\Upp\Calculation\Pricing\ExtraAmountCalculator;
use Aptenex\Upp\Calculation\Pricing\ModifierRateCalculator;
use Aptenex\Upp\Calculation\Pricing\PetsCalculator;
use Aptenex\Upp\Calculation\Pricing\Rate;
use Aptenex\Upp\Calculation\Pricing\Strategy\DaysOfWeekAlterationStrategy;
use Aptenex\Upp\Calculation\Pricing\Strategy\ExtraMonthsAlterationStrategy;
use Aptenex\Upp\Calculation\Pricing\Strategy\ExtraNightsAlterationStrategy;
use Aptenex\Upp\Calculation\Pricing\Strategy\PartialWeekAlterationStrategy;
use Aptenex\Upp\Calculation\Pricing\Strategy\PriceAlterationInterface;
use Aptenex\Upp\Calculation\Pricing\TaxesCalculator;
use Aptenex\Upp\Calculation\SplitAmount\GuestSplitOverview;
use Aptenex\Upp\Calculation\SplitAmount\SplitAmountProcessor;
use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Exception\CannotBookDatesException;
use Aptenex\Upp\Exception\CannotMatchRequestedDatesException;
use Aptenex\Upp\Exception\Error;
use Aptenex\Upp\Exception\ErrorHandler;
use Aptenex\Upp\Exception\InvalidPriceException;
use Aptenex\Upp\Helper\LanguageTools;
use Aptenex\Upp\Helper\MoneyTools;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Parser\Structure\PricingConfig;
use Aptenex\Upp\Parser\Structure\SplitMethod;
use Aptenex\Upp\Util\ArrayUtils;
use Aptenex\Upp\Util\ExceptionUtils;
use Aptenex\Upp\Util\MoneyUtils;
use Los\Modifier\ModifierExtractor;

class PricingGenerator
{

    /**
     * @param PricingContext $context
     * @param PricingConfig  $config
     *
     * @return FinalPrice
     * @throws CannotMatchRequestedDatesException
     * @throws InvalidPriceException
     */
    public function generate(PricingContext $context, PricingConfig $config)
    {
        $fp = new FinalPrice($context, $config);

        // First lets evaluate if we can even generate a price due to things like booking too close to arrival
        // In the future we will set the bookable field to be false but for now throw an exception as its the most
        // reliable way to handle not allowing a booking rentivo side
        $this->determineIfBookable($context, $fp);

        // Calculate the conditions and assign accordingly

        $this->evaluatePeriods($context, $fp);
        $this->validateDaysMatched($fp);
        $this->runPostEvaluationOnValidPeriods($fp);

        $this->evaluateModifiers($context, $fp);

        // ACTUAL PRICE CALCULATION STARTS NOW
        // This will iterate through the Days and assign each day their respective cost using the (calculated?) nightly
        // rate. This does not include any fancy progressive discounts etc and no modifiers are included yet

        $this->calculateBasicPrice($fp);

        // This will apply the costs to each night
        $this->applyPeriodStrategyAlterations($context, $fp);

        // Re-loop through the nights and re-total
        $this->calculateBasePrice($fp);
        $this->applyModifiers($context, $fp);
        $this->calculateBasePrice($fp);

        $this->calculateExtras($fp);

        $this->calculatePets($context, $fp);

        $this->calculateTaxes($context, $fp);

        $this->calculateDamageDeposit($fp);

        $this->calculateHiddenOnBase($fp);

        $this->calculateBaseNonTaxable($fp);

        $this->calculateTotalAndFinalBase($fp);

        $this->calculateSplitAmounts($context, $fp);

        /*
         * This will go through all periods used and determine what bookable type this price should be,
         * the order of precedence is enquiry only, then with price and then instant bookable
         */
        $this->determineBookableTypeAndFields($context, $fp);

        /*
         * The final check, just to make sure this isn't a messed up price
         */
        $this->performSanityChecks($context, $fp);

        return $fp;
    }

    /**
     * @param PricingContext $context
     * @param FinalPrice     $fp
     *
     * @throws InvalidPriceException
     */
    private function performSanityChecks(PricingContext $context, FinalPrice $fp)
    {
        if ($fp->getTotal()->isNegative()) {
            throw new InvalidPriceException(LanguageTools::trans('INVALID_PRICE'));
        }

        if ($context->isLosCalculationMode() === false && $fp->getSplitDetails() !== null) {
            if ($fp->getSplitDetails()->getDeposit()->isNegative()) {
                throw new InvalidPriceException(LanguageTools::trans('INVALID_PRICE'));
            }

            if ($fp->getSplitDetails()->getBalance()->isNegative()) {
                throw new InvalidPriceException(LanguageTools::trans('INVALID_PRICE'));
            }

        }
    }

    private function determineIfBookable(PricingContext $context, FinalPrice $fp)
    {
        if ($context->isTestMode()) {
            return; // Do not check this in test mode
        }

        // LOS is not going to be generated every day so this will become out of date and inaccurate.
        // Days required in advance will need to be set when pushing ARI to the various otas
        if ($context->isLosCalculationMode()) {
            return;
        }

        $defaults = $fp->getCurrencyConfigUsed()->getDefaults();

        if ($defaults->hasDaysRequiredInAdvanceForBooking()) {
            // There is a limit now that we need to evaluate

            $daysInterval = sprintf("P%sD", (int)$defaults->getDaysRequiredInAdvanceForBooking());
            $earliestBookableDay = (clone $context->getBookingDateObj())->add(new \DateInterval($daysInterval));

            $arrivalDate = (clone $context->getArrivalDateObj());

            // If the day is later than arrival date - reject
            if ($earliestBookableDay > $arrivalDate) {
                ExceptionUtils::handleErrorException(
                    new CannotBookDatesException(LanguageTools::trans('CANNOT_BOOK_TOO_CLOSE_TO_ARRIVAL')),
                    $fp,
                    Error::TYPE_MIN_ADVANCED_NOTICE_NOT_MET,
                    $defaults->getDaysRequiredInAdvanceForBooking()
                );
            }
        }

        if ($context->hasRentalSchemaData()) {
            $schema = $context->getRentalSchemaData();
            $listingKey = 'listing.maxOccupancy';
            if (ArrayUtils::hasNestedArrayValue($listingKey, $schema)) {

                $maxOccupancy = (int)ArrayUtils::getNestedArrayValue($listingKey, $schema);
                if ($context->getGuests() > $maxOccupancy) {
                    ExceptionUtils::handleError($fp, Error::TYPE_EXCEEDS_MAX_OCCUPANCY, $maxOccupancy);
                }

            }
        }
    }

    /**
     * @param PricingContext $context
     * @param FinalPrice     $fp
     */
    private function applyPeriodStrategyAlterations($context, $fp)
    {
        /** @var PriceAlterationInterface[] $alterationStrategies */
        $alterationStrategies = [
            new PartialWeekAlterationStrategy(),
            new ExtraNightsAlterationStrategy(),
            new ExtraMonthsAlterationStrategy(),
            new DaysOfWeekAlterationStrategy(),
        ];

        foreach ($fp->getStay()->getPeriodsUsed() as $period) {
            foreach ($alterationStrategies as $aS) {
                if ($aS->canAlter($context, $period, $fp)) {
                    $aS->alterPrice($context, $period, $fp);
                }

                $aS->postAlter($context, $period, $fp);
            }
        }
    }

    /**
     * @param FinalPrice $fp
     */
    private function calculateBasicPrice(FinalPrice $fp)
    {
        (new BasicRateCalculator())->compute($fp);

        $this->calculateBasePrice($fp);
    }

    /**
     * @param FinalPrice $fp
     */
    private function calculateExtras(FinalPrice $fp)
    {
        (new ExtraAmountCalculator())->calculateAndApplyAdjustments($fp);
    }

    /**
     * @param PricingContext $context
     * @param FinalPrice $fp
     */
    private function calculatePets(PricingContext $context, FinalPrice $fp): void
    {
        // If mode is los, this needs to be skipped as when sending ARI these will be sent separately
        if ($context->isLosCalculationMode()) {
            return;
        }

        (new PetsCalculator())->calculateAndApplyAdjustments($fp);
    }

    /**
     * @param PricingContext $context
     * @param FinalPrice $fp
     */
    private function calculateTaxes(PricingContext $context, FinalPrice $fp): void
    {
        // If mode is los, this needs to be skipped as when sending ARI these will be sent separately
        if ($context->getCalculationMode() === PricingContext::CALCULATION_MODE_LOS_EXCLUDE_MANDATORY_FEES_AND_TAXES) {
            return;
        }

        (new TaxesCalculator())->calculateAndApplyAdjustments($fp);
    }

    /**
     * This is applied after taxes and is added as another adjustment
     *
     * @param FinalPrice $fp
     */
    private function calculateDamageDeposit(FinalPrice $fp): void
    {
        (new DamageDepositCalculator())->calculateAndApplyAdjustment($fp);
    }

    /**
     * @param FinalPrice $fp
     */
    private function calculateBasePrice(FinalPrice $fp): void
    {
        $zero = MoneyUtils::newMoney(0, $fp->getBasePrice()->getCurrency());

        $nightMonies = [];
        foreach ($fp->getStay()->getNights() as $night) {
            $nightMonies[] = $night->getCost();
        }

        $fp->setBasePrice($zero->add(...$nightMonies));

        foreach ($fp->getAdjustments() as $adjustment) {
            switch ($adjustment->getPriceGroup()) {
                case AdjustmentAmount::PRICE_GROUP_BASE:
                    $fp->setBasePrice(MoneyTools::applyMonetaryOperand($fp->getBasePrice(), $adjustment->getAmount(), $adjustment->getOperand()));
                    break;
            }
        }
    }

    private function calculateHiddenOnBase(FinalPrice $fp): void
    {
        $new = MoneyUtils::newMoney(0, $fp->getCurrency());

        $new = $new->add($fp->getBasePrice());

        foreach ($fp->getAdjustments() as $adjustment) {
            switch ($adjustment->getPriceGroup()) {
                case AdjustmentAmount::PRICE_GROUP_HIDDEN_ON_BASE:
                    $new = MoneyTools::applyMonetaryOperand($new, $adjustment->getAmount(), $adjustment->getOperand());
                    break;
            }
        }

        $fp->setBasePrice($new);
    }

    private function calculateBaseNonTaxable(FinalPrice $fp): void
    {
        $new = MoneyUtils::newMoney(0, $fp->getCurrency());

        $new = $new->add($fp->getBasePrice());

        foreach ($fp->getAdjustments() as $adjustment) {
            switch ($adjustment->getPriceGroup()) {
                case AdjustmentAmount::PRICE_GROUP_BASE_NON_TAXABLE:
                    $new = MoneyTools::applyMonetaryOperand($new, $adjustment->getAmount(), $adjustment->getOperand());
                    break;
            }
        }

        $fp->setBasePrice($new);
    }

    private function calculateTotalAndFinalBase(FinalPrice $fp): void
    {
        $newTotal = MoneyUtils::newMoney(0, $fp->getCurrency());
        $newBase = MoneyUtils::newMoney($fp->getBasePrice()->getAmount(), $fp->getCurrency());

        $newTotal = $newTotal->add($fp->getBasePrice());

        foreach ($fp->getAdjustments() as $adjustment) {
            switch ($adjustment->getPriceGroup()) {
                case AdjustmentAmount::PRICE_GROUP_TOTAL:

                    $newTotal = MoneyTools::applyMonetaryOperand($newTotal, $adjustment->getAmount(), $adjustment->getOperand());

                    if (
                        $adjustment->getType() === AdjustmentAmount::TYPE_MODIFIER &&
                        $adjustment->getOperand() === Operand::OP_SUBTRACTION
                    ) {
                        // Here, since its a subtraction we've also got to modify the base too
                        $newBase = MoneyTools::applyMonetaryOperand($newBase, $adjustment->getAmount(), $adjustment->getOperand());
                    }

                    break;
            }
        }

        $fp->setBasePrice($newBase);
        $fp->setTotal($newTotal);
    }

    /**
     * This will alter the relevant days based on the modifiers
     *
     * @param PricingContext $context
     * @param FinalPrice $fp
     */
    private function applyModifiers(PricingContext $context, FinalPrice $fp): void
    {
        (new ModifierRateCalculator())->compute($context, $fp);
    }

    /**
     * @param PricingContext $context
     * @param FinalPrice     $fp
     */
    private function evaluatePeriods(PricingContext $context, FinalPrice $fp): void
    {
        foreach ($fp->getCurrencyConfigUsed()->getPeriods() as $period) {

            $cp = new Period($fp);
            $cp->setControlItemConfig($period);

            $conSet = (new Evaluator())->evaluateConditions($context, $cp);

            $cp->setConditions($conSet);

            if (!$conSet->hasDateCondition()) {
                continue;
            }

            if ($conSet->isValidConditionSet()) {

                $cp->setMatched(true);

                // We need to assign any 'matched days' to the Stay/Day object
                // Since the periods are in order of priority, if a day object already
                // has a period attached to it, then it will be ignored.

                // If all days have been taken up then cancel the period condition evaluation as we have
                // all that we need

                foreach ($fp->getStay()->getNights() as $date => $day) {
                    if ($day->hasPeriodControlItem()) {
                        continue;
                    }

                    // If the stay's day date exists in this condition collection set then add it..
                    if (!array_key_exists($date, $conSet->getMatchedDates())) {
                        continue;
                    }

                    $day->setPeriodControlItem($conSet->getMatchedDates()[$date]->getControlItem());

                    $cp->addMatchedNight($day);
                    $fp->getStay()->addPeriodsUsed($cp);
                }

            }
        }
    }

    /**
     * @param PricingContext $context
     * @param FinalPrice     $fp
     */
    private function evaluateModifiers(PricingContext $context, FinalPrice $fp): void
    {
        $me = new ModifierExtractor();

        foreach ($fp->getCurrencyConfigUsed()->getModifiers() as $modifier) {

            $cm = new Modifier($fp);
            $cm->setControlItemConfig($modifier);

            /*
             * If the mode is to exclude fees & taxes, then we need to check if this modifier has any conditions.
             *
             * If it does not, then skip this modifier as it will be sent to the OTA separately on the ARI push.
             * This is to stop commissions by the OTA's being taken on tax amounts etc...
             */

            if ($me->isModifierSupportedByMode($context->getCalculationMode(), $modifier) === false) {
                continue;
            }

            $conSet = (new Evaluator())->evaluateConditions($context, $cm);

            // Already set in evaluate conditions
            $cm->setConditions($conSet);

            // We will handle any 'global' modifiers as an addition that can be displayed
            if ($conSet->isValidConditionSet()) {

                $cm->setMatched(true);

                if ($cm->isGlobal()) {

                    $fp->getStay()->addModifiersUsed($cm);

                } else {

                    // We need to assign any 'matched days' to the Stay/Day object
                    // Since the periods are in order of priority, if a day object already
                    // has a period attached to it, then it will be ignored.

                    // If all days have been taken up then cancel the period condition evaluation as we have
                    // all that we need

                    foreach ($fp->getStay()->getNights() as $date => $day) {

                        // If the stay's day date exists in this condition collection set then add it..
                        if (!array_key_exists($date, $conSet->getMatchedDates())) {
                            continue;
                        }

                        $day->addModifierControlItem($conSet->getMatchedDates()[$date]->getControlItem());
                        $cm->addMatchedNight($day);
                        $fp->getStay()->addModifiersUsed($cm);

                    }

                }

            }
        }
    }

    /**
     * @param FinalPrice $fp
     *
     * @throws CannotMatchRequestedDatesException
     */
    private function validateDaysMatched(FinalPrice $fp): void
    {
        $notMatched = [];

        foreach ($fp->getStay()->getNights() as $day) {
            if (!$day->hasPeriodControlItem()) {
                $notMatched[] = $day->getDate()->format('Y-m-d');
            }
        }

        if (!empty($notMatched)) {
            throw new CannotMatchRequestedDatesException(LanguageTools::trans('NO_PERIOD_MATCHED'));
        }
    }

    /**
     * @param FinalPrice $fp
     */
    private function runPostEvaluationOnValidPeriods(FinalPrice $fp): void
    {
        $this->performMinimumNightsCheck($fp);

        // Loop through any failures and report back
        foreach ($fp->getStay()->getPeriodsUsed() as $period) {
            $e = new Evaluator();
            $e->evaluatePostConditions($fp->getContextUsed(), $period);

            if (!empty($period->getFailuresIfMatched())) {
                $cmEx = new CannotMatchRequestedDatesException(implode(' ', $period->getFailuresIfMatched()));

                ExceptionUtils::handleException($cmEx, $fp);
            }
        }
    }

    /**
     * @param FinalPrice $fp
     */
    private function performMinimumNightsCheck(FinalPrice $fp): void
    {
        $defaults = $fp->getCurrencyConfigUsed()->getDefaults();

        // The previous functionality was to get the arrival day period and use that minimum nights
        // to either error or continue. The new functionality will error out if the period with the
        // highest minimum nights is matched and not met.

        $useHighestMinimumNights = true;
        if ($useHighestMinimumNights) {
            $minimumNights = 0;
            if ($defaults->hasMinimumNights() && $minimumNights < $defaults->getMinimumNights()) {
                $minimumNights = $defaults->getMinimumNights();
            }

            foreach ($fp->getStay()->getPeriodsUsed() as $period) {
                $config = $period->getControlItemConfig();
                if ($config->hasMinimumNights()) {
                    $minimumNights = $config->getMinimumNights();
                }
            }

            if ($minimumNights > 0 && $minimumNights > $fp->getStay()->getNoNights()) {
                $cmEx = new CannotMatchRequestedDatesException(LanguageTools::trans('MINIMUM_NIGHTS', [
                    '%minimumNights%'  => $minimumNights,
                    '%selectedNights%' => $fp->getStay()->getNoNights()
                ]));

                ExceptionUtils::handleErrorException($cmEx, $fp, Error::TYPE_MIN_STAY_NOT_MET, $minimumNights);
            }
        } else {
            foreach ($fp->getStay()->getPeriodsUsed() as $period) {
                if ($period->containsArrivalDayInMatchedNights()) {

                    // Minimum Nights

                    $minimumNights = null;

                    if ($defaults->hasMinimumNights()) {
                        $minimumNights = $defaults->getMinimumNights();
                    }

                    if ($period->getControlItemConfig()->hasMinimumNights()) {
                        $minimumNights = $period->getControlItemConfig()->getMinimumNights();
                    }

                    if (!empty($minimumNights) && $minimumNights > $fp->getStay()->getNoNights()) {
                        $cmEx = new CannotMatchRequestedDatesException(LanguageTools::trans('MINIMUM_NIGHTS', [
                            '%minimumNights%'  => $minimumNights,
                            '%selectedNights%' => $fp->getStay()->getNoNights()
                        ]));

                        ExceptionUtils::handleErrorException($cmEx, $fp, Error::TYPE_MIN_STAY_NOT_MET, $minimumNights);
                    }

                }
            }
        }
    }

    private function calculateSplitAmounts(PricingContext $context, FinalPrice $fp): void
    {
        if ($context->isLosCalculationMode()) {
            return;
        }

        $defaults = $fp->getCurrencyConfigUsed()->getDefaults();

        if ($defaults->hasDamageDeposit()) {
            $fp->setDamageDepositSplitMethod($defaults->getDamageDepositSplitMethod());
        }

        // There is no arrival date set. Perhaps forceGeneration is enabled and arrival date is same date and departure?
        if (!$fp->getStay()->getPeriodWithArrivalDay()) {
            $fp->disableSplitDetails();

            return;
        }

        $arrivalPeriodRate = $fp->getStay()->getPeriodWithArrivalDay()->getRate();

        $depositFixed = 0;
        $depositSplitPercentage = $defaults->getDepositSplitPercentage();

        if ($arrivalPeriodRate->hasDepositOverride()) {
            $depositFixed = MoneyUtils::getConvertedAmount($arrivalPeriodRate->getDepositOverride());
            $depositSplitPercentage = 0; // This needs to be overridden
        }

        if (!$defaults->hasDepositSplitPercentage() && $depositFixed == 0) {
            $fp->disableSplitDetails();

            return; // Stop execution
        }

        $sap = new SplitAmountProcessor($fp);

        $spr = $sap->computeSplitAmount(
            $fp->getTotal(),
            $depositSplitPercentage,
            $depositFixed,
            $fp->getDamageDeposit(),
            $defaults->getDamageDepositSplitMethod()
        );

        $ratio = [0, 100];
        if ($defaults->getDepositSplitPercentage() > 0) {
            $ratio = [$defaults->getDepositSplitPercentage(), 100 - $defaults->getDepositSplitPercentage()];
        }

        // Now we need to go through the adjustments and see if we need to adjust the split
        foreach ($fp->getAdjustments() as $adjustment) {

            if ($adjustment->getOperand() === Operand::OP_ADDITION) {
                $negate = Operand::OP_SUBTRACTION;
            } else if ($adjustment->getOperand() === Operand::OP_SUBTRACTION) {
                $negate = Operand::OP_ADDITION;
            } else {
                $negate = Operand::OP_EQUALS;
            }

            switch ($adjustment->getSplitMethod()) {

                case SplitMethod::ON_TOTAL:

                    // Do nothing as its already been split automatically

                    break;

                case SplitMethod::ON_DEPOSIT:

                    list($sDeposit, $sBalance) = $adjustment->getAmount()->allocate($ratio);

                    // WE HAVE GOT THE SPLITS THAT WENT ONTO THE DEPOSIT / BALANCE
                    // NOW WE NEED PUT THE BALANCE BACK TO NORMAL AND ADD IT TO THE DEPOSIT

                    $spr->setDeposit(MoneyTools::applyMonetaryOperand(
                        $spr->getDeposit(),
                        $sBalance,
                        $adjustment->getOperand()
                    ));

                    $spr->setBalance(MoneyTools::applyMonetaryOperand(
                        $spr->getBalance(),
                        $sBalance,
                        $negate
                    ));

                    break;

                case SplitMethod::ON_BALANCE:

                    list($sDeposit, $sBalance) = $adjustment->getAmount()->allocate($ratio);

                    $spr->setDeposit(MoneyTools::applyMonetaryOperand(
                        $spr->getDeposit(),
                        $sDeposit,
                        $negate
                    ));

                    $spr->setBalance(MoneyTools::applyMonetaryOperand(
                        $spr->getBalance(),
                        $sDeposit,
                        $adjustment->getOperand()
                    ));

                    break;

            }

        }


        $fp->getSplitDetails()->setDeposit($spr->getDeposit());

        if ($depositFixed > 0) {
            // We need to fix this if the deposit has had some special calculations involved.
            $fp->getSplitDetails()->setDepositCalculationType(GuestSplitOverview::DEPOSIT_CALCULATION_TYPE_FIXED);
        }

        $fp->getSplitDetails()->setBalance($spr->getBalance());

        $fp->getSplitDetails()->setDepositDueDate($this->calculateDepositDueDate());
        $fp->getSplitDetails()->setBalanceDueDate($this->calculateBalanceDueDate(
            $defaults->getBalanceDaysBeforeArrival(),
            $fp->getStay()->getArrival()
        ));

        if ($defaults->hasDamageDeposit()) {
            $fp->getSplitDetails()->setDamageDepositSplitMethod($defaults->getDamageDepositSplitMethod());
        }
    }

    /**
     * Decides which bookable type to set, this is only really applicable when there are multiple periods
     *
     * @param FinalPrice $fp
     */
    private function determineBookableTypeAndFields(PricingContext $context, FinalPrice $fp): void
    {
        if ($context->isLosCalculationMode()) {
            return;
        }

        $priorityMap = \Aptenex\Upp\Parser\Structure\Period::$bookableTypePriorityMap;

        $current = null; // So first bookable type will always work
        $currentPriority = -1;

        foreach ($fp->getStay()->getPeriodsUsed() as $pUsed) {
            $pCurrent = $pUsed->getControlItemConfig()->getBookableType();

            if (is_null($pCurrent) || empty($pCurrent)) {
                $pCurrent = $fp->getCurrencyConfigUsed()->getDefaults()->getBookableType(); // Use defaults if blank
            }

            if (is_null($pCurrent)) {
                // Default was also blank set to default default
                $pCurrent = \Aptenex\Upp\Parser\Structure\Period::BOOKABLE_TYPE_DEFAULT;
            }

            if (!isset($priorityMap[$pCurrent])) {
                continue;
            }

            $pPriority = $priorityMap[$pCurrent];

            if ($pPriority > $currentPriority) {
                $current = $pCurrent;
                $currentPriority = $pPriority;
            }
        }

        $fp->setBookableType($current);
    }

    /**
     * Deposit is always due immediately
     *
     * @return \DateTime
     */
    public function calculateDepositDueDate(): \DateTime
    {
        return new \DateTime(date("Y-m-d"), new \DateTimeZone('UTC'));
    }

    /**
     * @param int       $balanceDaysBeforeArrival
     * @param \DateTime $arrivalDate
     *
     * @return \DateTime
     * @throws \Exception
     */
    public function calculateBalanceDueDate($balanceDaysBeforeArrival, $arrivalDate): \DateTime
    {
        $arrivalDate = clone $arrivalDate;

        return $arrivalDate->sub(new \DateInterval(sprintf("P%sD", $balanceDaysBeforeArrival)));
    }

}