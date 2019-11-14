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

        /**
         * @var float|null $lowPeriodAmount
         * @var float|null $highPeriodAmount
         */
        $lowPeriodAmount = null;
        $highPeriodAmount = null;

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
                if ($lowPeriodAmount === null) {
                    $lowPeriodAmount = $this->getLowestRoughlyNightlyAmount($period);
                    $highPeriodAmount = $this->getHighestRoughlyNightlyAmount($period);
                } else {
                    // Get the nightly version
                    $currentLowPeriodAmount = $this->getLowestRoughlyNightlyAmount($period);
                    $currentHighPeriodAmount = $this->getHighestRoughlyNightlyAmount($period);

                    if ($currentLowPeriodAmount < $lowPeriodAmount) {
                        $lowPeriodAmount = $currentLowPeriodAmount;
                    }

                    if ($currentHighPeriodAmount > $highPeriodAmount) {
                        $highPeriodAmount = $currentHighPeriodAmount;
                    }
                }
                
            }

            if ($lowPeriodAmount !== null && $lowPeriodAmount > 0) {
                $visual['nightlyLow'] = $lowPeriodAmount;
            }

            if ($highPeriodAmount !== null && $highPeriodAmount > 0) {
                $visual['nightlyHigh'] = $highPeriodAmount;
            }

            $visual['nightlyLow'] = (int) round($visual['nightlyLow']);
            $visual['nightlyHigh'] = (int) round($visual['nightlyHigh']);
            $visual['weeklyLow'] = (int) round($visual['nightlyLow'] * 7);
            $visual['weeklyHigh'] = (int) round($visual['nightlyHigh'] * 7);

            return $visual;

        } catch (\Exception $ex) {
            throw new InvalidPricingConfigException('Could not transform pricing - ' .$ex->getMessage(), 0, $ex);
        }
    }

    private function getLowestRoughlyNightlyAmount(Period $period)
    {
        $activeStrategy = $period->getRate()->getStrategy()->getActiveStrategy();

        if ($activeStrategy === null || !($activeStrategy instanceof ExtraNightsAlteration)) {
            return $period->getRate()->getRoughNightlyAmount();
        }

        $brackets = $activeStrategy->getBrackets();

        if (empty($brackets)) {
            return $period->getRate()->getRoughNightlyAmount();
        }

        $expandedBrackets = (new BracketsEvaluator())->expandBrackets($activeStrategy->getBrackets(), 30, true);

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

    private function getHighestRoughlyNightlyAmount(Period $period)
    {
        $activeStrategy = $period->getRate()->getStrategy()->getActiveStrategy();

        if ($activeStrategy === null || !($activeStrategy instanceof ExtraNightsAlteration)) {
            return $period->getRate()->getRoughNightlyAmount();
        }

        $brackets = $activeStrategy->getBrackets();

        if (empty($brackets)) {
            return $period->getRate()->getRoughNightlyAmount();
        }

        $expandedBrackets = (new BracketsEvaluator())->expandBrackets($activeStrategy->getBrackets(), 30, true);

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