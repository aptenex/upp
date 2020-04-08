<?php

namespace Aptenex\Upp\Transformer;

use Aptenex\Upp\Calculation\Pricing\Strategy\BracketsEvaluator;
use Aptenex\Upp\Calculation\Pricing\Strategy\ExtraNightsAlterationStrategy;
use Aptenex\Upp\Exception\InvalidPricingConfigException;
use Aptenex\Upp\Parser\Structure\CurrencyConfig;
use Aptenex\Upp\Parser\Structure\ExtraNightsAlteration;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Parser\Structure\Period;
use Aptenex\Upp\Parser\Structure\PricingConfig;
use Aptenex\Upp\Parser\Structure\Rate;

/**
 * This will transform the UPP schema into the lycan property schema's visual pricing section. It will
 * take into account the brackets on each season to try and find the "lowest" technical price
 */
class LycanVisualPricingTransformer implements TransformerInterface
{

    /**
     * @var bool
     */
	private $includeExpiredPeriods;

	public function __construct($includeExpiredPeriods = false)
	{
		$this->includeExpiredPeriods = $includeExpiredPeriods;
	}
	
	/**
     * @param PricingConfig $config
     *
     * @return array
     * @throws InvalidPricingConfigException
     */
    public function transform(PricingConfig $config)
    {
        if (empty($config->getCurrencyConfigs())) {
            throw new InvalidPricingConfigException('Cannot transform empty pricing');
        }

        /** @var CurrencyConfig $c */
        $c = array_values($config->getCurrencyConfigs())[0];

        $visual = [
            'currency' => $c->getCurrency(),
            'nightlyLow' => null,
            'nightlyHigh' => null,
            'weeklyLow' => null,
            'weeklyHigh' => null
        ];

        $lowPeriodAmountNightly = null;
        $lowPeriodAmountWeekly = null;

        $highPeriodAmountNightly = null;
        $highPeriodAmountWeekly = null;

        try {

            foreach ($c->getPeriods() as $period) {
                $dCondition = $period->getDateCondition();

                try {
                    $now = new \DateTime();
                    $endDate = new \DateTime($dCondition->getEndDate());
					
                    if ($now > $endDate && !$this->includeExpiredPeriods) {
                        continue; // Don't send any period that is in the past
                    }
                } catch (\Exception $ex) {
                    // Carry on - better than not showing any rates
                }

                // First lets determine the cheapest and most expensive periods
                if ($lowPeriodAmountNightly === null) {
                    $lowPeriodAmountNightly = $this->getLowestRoughAmount($period);
                    $highPeriodAmountNightly = $this->getHighestRoughAmount($period);

                    $lowPeriodAmountWeekly = $this->getLowestRoughAmount($period, true);
                    $highPeriodAmountWeekly = $this->getHighestRoughAmount($period, true);
                } else {
                    // Get the nightly version
                    $currentLowPeriodAmountNightly = $this->getLowestRoughAmount($period);
                    $currentHighPeriodAmountNightly = $this->getHighestRoughAmount($period);

                    if ($currentLowPeriodAmountNightly < $lowPeriodAmountNightly) {
                        $lowPeriodAmountNightly = $currentLowPeriodAmountNightly;
                    }

                    if ($currentLowPeriodAmountNightly > $highPeriodAmountNightly) {
                        $highPeriodAmountNightly = $currentHighPeriodAmountNightly;
                    }

                    // Get the weekly version
                    $currentLowPeriodAmountWeekly = $this->getLowestRoughAmount($period, true);
                    $currentHighPeriodAmountWeekly = $this->getHighestRoughAmount($period, true);

                    if ($currentLowPeriodAmountWeekly < $lowPeriodAmountWeekly) {
                        $lowPeriodAmountWeekly = $currentLowPeriodAmountWeekly;
                    }

                    if ($currentHighPeriodAmountWeekly > $highPeriodAmountWeekly) {
                        $highPeriodAmountWeekly = $currentHighPeriodAmountWeekly;
                    }
                }
            }

            if ($lowPeriodAmountNightly !== null && $lowPeriodAmountNightly > 0) {
                $visual['nightlyLow'] = $lowPeriodAmountNightly;
            }

            if ($highPeriodAmountNightly !== null && $highPeriodAmountNightly > 0) {
                $visual['nightlyHigh'] = $highPeriodAmountNightly;
            }

            if ($lowPeriodAmountWeekly !== null && $lowPeriodAmountWeekly > 0) {
                $visual['weeklyLow'] = $lowPeriodAmountWeekly;
            }

            if ($highPeriodAmountWeekly !== null && $highPeriodAmountWeekly > 0) {
                $visual['weeklyHigh'] = $highPeriodAmountWeekly;
            }

            $visual['nightlyLow'] = (int) round($visual['nightlyLow']);
            $visual['nightlyHigh'] = (int) round($visual['nightlyHigh']);
            $visual['weeklyLow'] = (int) round($visual['weeklyLow']);
            $visual['weeklyHigh'] = (int) round($visual['weeklyHigh']);

            return $visual;

        } catch (\Exception $ex) {
            throw new InvalidPricingConfigException('Could not transform pricing - ' .$ex->getMessage(), 0, $ex);
        }
    }

    private function getLowestRoughAmount(Period $period, $forWeek = false)
    {
        $activeStrategy = null;

        if ($period->getRate() !== null && $period->getRate()->getStrategy() !== null) {
            $activeStrategy = $period->getRate()->getStrategy()->getActiveStrategy();
        }

        if ($activeStrategy === null || !($activeStrategy instanceof ExtraNightsAlteration)) {
            if ($forWeek) {
                if ($period->getRate()->getType() === Rate::TYPE_WEEKLY) {
                    return $period->getRate()->getAmount();
                } else {
                    return $period->getRate()->getRoughNightlyAmount() * 7;
                }
            } else {
                return $period->getRate()->getRoughNightlyAmount();
            }
        }

        $be = new BracketsEvaluator();

        $brackets = $activeStrategy->getBrackets();

        $nights = $forWeek ? 7 : 30;

        if (!$be->hasAtLeastOneMatch($brackets, $nights)) {
            return $period->getRate()->getRoughNightlyAmount();
        }

        if ($forWeek) {
            return $this->calculateWeek($be, $nights, $activeStrategy, $period);
        }

        $expandedBrackets = (new BracketsEvaluator())->expandBrackets($activeStrategy->getBrackets(), $nights, true);

        $cheapest = $period->getRate()->getRoughNightlyAmount();
        foreach($expandedBrackets as $bracket) {
            $moneyAmount = $this->getMonetaryFigure($period->getRate(), $activeStrategy, $bracket['amount']);

            if ($moneyAmount < $cheapest) {
                $cheapest = $moneyAmount;
            }
        }

        return $cheapest;
    }

    private function getHighestRoughAmount(Period $period, $forWeek = false)
    {
        $activeStrategy = null;

        if ($period->getRate() !== null && $period->getRate()->getStrategy() !== null) {
            $activeStrategy = $period->getRate()->getStrategy()->getActiveStrategy();
        }

        if ($activeStrategy === null || !($activeStrategy instanceof ExtraNightsAlteration)) {
            if ($forWeek) {
                if ($period->getRate()->getType() === Rate::TYPE_WEEKLY) {
                    return $period->getRate()->getAmount();
                } else {
                    return $period->getRate()->getRoughNightlyAmount() * 7;
                }
            } else {
                return $period->getRate()->getRoughNightlyAmount();
            }
        }

        $be = new BracketsEvaluator();

        $brackets = $activeStrategy->getBrackets();

        $nights = $forWeek ? 7 : 30;

        if (!$be->hasAtLeastOneMatch($brackets, $nights)) {
            return $period->getRate()->getRoughNightlyAmount();
        }

        if ($forWeek) {
            return $this->calculateWeek($be, $nights, $activeStrategy, $period);
        }

        $expandedBrackets = (new BracketsEvaluator())->expandBrackets($activeStrategy->getBrackets(), $nights, true);

        $expensive = $period->getRate()->getRoughNightlyAmount();
        foreach ($expandedBrackets as $bracket) {
            $moneyAmount = $this->getMonetaryFigure($period->getRate(), $activeStrategy, $bracket['amount']);

            if ($moneyAmount > $expensive) {
                $expensive = $moneyAmount;
            }
        }

        return $expensive;
    }

    private function calculateWeek(BracketsEvaluator $be, int $nights, ExtraNightsAlteration $activeStrategy, Period $period)
    {
        if ($period->getRate()->getType() === Rate::TYPE_WEEKLY) {
            return $period->getRate()->getAmount();
        }

        $bracketDayValueMap = $be->retrieveExtraNightsDiscountValues($activeStrategy->getBrackets(), $nights);

        // We need to sort this in case a lower bracket is added after a high one with
        // extraNightsAlterationStrategyUseGlobalNights being enabled as this causes issues
        ksort($bracketDayValueMap);

        // No brackets so no point in looping
        // times by 7 as its nightly
        if (empty($bracketDayValueMap)) {
            return $period->getRate()->getRoughNightlyAmount() * 7;
        }

        $enas = new ExtraNightsAlterationStrategy();

        $totalCost = 0;

        for ($i = 0;  $i < $nights; $i++) {

            $baseNightAmount = $period->getRate()->getAmount();

            $value = $enas->getNightlyValue(
                $i + 1,
                0,
                (float) $baseNightAmount, // Convert in-case it is null
                $bracketDayValueMap,
                $activeStrategy
            );

            if ($value !== null) {
                switch ($activeStrategy->getCalculationOperator()) {
                    case Operand::OP_ADDITION:
                        $baseNightAmount += $value;

                        break;

                    case Operand::OP_SUBTRACTION:
                        $baseNightAmount -= $value;

                        break;

                    case Operand::OP_EQUALS:
                    default:
                        $baseNightAmount =  $value;

                }
            }

            $totalCost += $baseNightAmount;
        }

        return $totalCost;
    }

    private function getMonetaryFigure(Rate $rate, ExtraNightsAlteration $activeStrategy, $bracketAmount)
    {
        /** @var ExtraNightsAlteration $activeStrategy */
        if ($activeStrategy->getCalculationMethod() === Rate::METHOD_PERCENTAGE) {
            $bracketAmount = $rate->getAmount() * $bracketAmount;
        }

        if ($activeStrategy->getCalculationOperator() === Operand::OP_SUBTRACTION) {
            // We actually need to subtract this off the rate
            $bracketAmount = $rate->getAmount() - $bracketAmount;
        } else if ($activeStrategy->getCalculationOperator() === Operand::OP_ADDITION) {
            $bracketAmount = $rate->getAmount() + $bracketAmount;
        } else if ($activeStrategy->getCalculationOperator() === Operand::OP_EQUALS) {
            $bracketAmount = $bracketAmount;
        }

        return $bracketAmount;
    }

}