<?php

namespace Aptenex\Upp\Transformer;

use Aptenex\Upp\Calculation\Pricing\Strategy\BracketsEvaluator;
use Aptenex\Upp\Exception\InvalidPricingConfigException;
use Aptenex\Upp\Parser\Structure\CurrencyConfig;
use Aptenex\Upp\Parser\Structure\ExtraNightsAlteration;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Parser\Structure\PartialWeekAlteration;
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
                    $lowPeriodAmountNightly = $this->getLowestRoughlyNightlyAmount($period);
                    $lowPeriodAmountWeekly = $this->getLowestRoughlyNightlyAmount($period) * 7;

                    $highPeriodAmountNightly = $this->getHighestRoughlyNightlyAmount($period);
                    $highPeriodAmountWeekly = $this->getHighestRoughlyNightlyAmount($period) * 7;
                } else {
                    // Get the nightly version
                    $currentLowPeriodAmountNightly = $this->getLowestRoughlyNightlyAmount($period);
                    $currentHighPeriodAmountNightly = $this->getHighestRoughlyNightlyAmount($period);

                    if ($currentLowPeriodAmountNightly < $lowPeriodAmountNightly) {
                        $lowPeriodAmountNightly = $currentLowPeriodAmountNightly;
                    }

                    if ($currentLowPeriodAmountNightly > $highPeriodAmountNightly) {
                        $highPeriodAmountNightly = $currentHighPeriodAmountNightly;
                    }

                    // Get the weekly version
                    $currentLowPeriodAmountWeekly = $this->getLowestRoughlyNightlyAmount($period) * 7;
                    $currentHighPeriodAmountWeekly = $this->getHighestRoughlyNightlyAmount($period) * 7;

                    if ($currentLowPeriodAmountWeekly < $lowPeriodAmountWeekly) {
                        $lowPeriodAmountWeekly = $currentLowPeriodAmountWeekly;
                    }

                    if ($currentLowPeriodAmountWeekly > $highPeriodAmountWeekly) {
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

    private function getLowestRoughlyNightlyAmount(Period $period, $nights = 30)
    {
        $activeStrategy = null;

        if ($period->getRate() !== null && $period->getRate()->getStrategy() !== null) {
            $activeStrategy = $period->getRate()->getStrategy()->getActiveStrategy();
        }

        if ($activeStrategy === null || !($activeStrategy instanceof ExtraNightsAlteration)) {
            return $period->getRate()->getRoughNightlyAmount();
        }

        $brackets = $activeStrategy->getBrackets();

        if (empty($brackets)) {
            return $period->getRate()->getRoughNightlyAmount();
        }

        $expandedBrackets = (new BracketsEvaluator())->expandBrackets($activeStrategy->getBrackets(), $nights, true);

        // Set the cheapest to the rough nightly amount
        $cheapest = $period->getRate()->getRoughNightlyAmount();
        foreach($expandedBrackets as $bracket) {
            $moneyAmount = $this->getMonetaryFigure($period->getRate(), $bracket['amount']);

            if ($moneyAmount < $cheapest) {
                $cheapest = $moneyAmount;
            }
        }

        return $cheapest;
    }

    private function getHighestRoughlyNightlyAmount(Period $period, $nights = 30)
    {
        $activeStrategy = null;

        if ($period->getRate() !== null && $period->getRate()->getStrategy() !== null) {
            $activeStrategy = $period->getRate()->getStrategy()->getActiveStrategy();
        }

        if ($activeStrategy === null || !($activeStrategy instanceof ExtraNightsAlteration)) {
            return $period->getRate()->getRoughNightlyAmount();
        }

        $brackets = $activeStrategy->getBrackets();

        if (empty($brackets)) {
            return $period->getRate()->getRoughNightlyAmount();
        }

        $expandedBrackets = (new BracketsEvaluator())->expandBrackets($activeStrategy->getBrackets(), $nights, true);

        // Set the expensive to the rough nightly amount
        $expensive = $period->getRate()->getRoughNightlyAmount();
        foreach($expandedBrackets as $bracket) {
            $moneyAmount = $this->getMonetaryFigure($period->getRate(), $bracket['amount']);

            if ($moneyAmount > $expensive) {
                $expensive = $moneyAmount;
            }
        }

        return $expensive;
    }

    private function getMonetaryFigure(Rate $rate, $bracketAmount)
    {
        if ($rate->getCalculationMethod() === Rate::METHOD_PERCENTAGE) {
            $bracketAmount = $rate->getAmount() * $bracketAmount;
        }

        if ($rate->getCalculationOperand() === Operand::OP_SUBTRACTION) {
            // We actually need to subtract this off the rate
            $bracketAmount = $rate->getAmount() - $bracketAmount;
        } else if ($rate->getCalculationOperand() === Operand::OP_ADDITION) {
            $bracketAmount = $rate->getAmount() + $bracketAmount;
        }

        return $bracketAmount;
    }

}