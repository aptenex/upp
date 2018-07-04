<?php

namespace Aptenex\Upp\Transformer;

use Aptenex\Upp\Exception\InvalidPricingConfigException;
use Aptenex\Upp\Parser\Structure\CurrencyConfig;
use Aptenex\Upp\Parser\Structure\Period;
use Aptenex\Upp\Parser\Structure\PricingConfig;
use Aptenex\Upp\Parser\Structure\Rate;

/**
 * This transformer will convert the UPP parsed pricing object into the rentivo rates format as accurately as possible.
 *
 * Any advanced settings that can't be moved over will be ignored
 */
class LowHighTransformer implements TransformerInterface
{

    /**
     * @param PricingConfig $config
     *
     * @return array
     * @throws InvalidPricingConfigException
     */
    public function transform(PricingConfig $config)
    {
        if (empty($config->getCurrencyConfigs())) {
            throw new InvalidPricingConfigException("Cannot transform empty pricing");
        }

        /** @var CurrencyConfig $c */
        $c = array_values($config->getCurrencyConfigs())[0];

        /**
         * @var Period|null $lowPeriod
         * @var Period|null $highPeriod
         */
        $lowPeriod = null;
        $highPeriod = null;

        $ranges = [];

        try {

            foreach ($c->getPeriods() as $period) {
                $dCondition = $period->getDateCondition();

                try {
                    $now = new \DateTime();
                    $endDate = new \DateTime($dCondition->getEndDate());

                    if ($now > $endDate) {
                        continue; // Don't send any period that is in the past
                    }
                } catch (\Exception $ex) {
                    // Carry on - better than not showing any rates
                }

                // First lets determine the cheapest and most expensive periods
                if (is_null($lowPeriod)) {
                    $lowPeriod = $period;
                    $highPeriod = $period;
                } else {
                    // Get the nightly version
                    if ($period->getRate()->getRoughNightlyAmount() < $lowPeriod->getRate()->getRoughNightlyAmount()) {
                        $lowPeriod = $period;
                    }

                    if ($period->getRate()->getRoughNightlyAmount() > $highPeriod->getRate()->getRoughNightlyAmount()) {
                        $highPeriod = $period;
                    }
                }
                
            }

            $returnData = [
				'currency'    => $c->getCurrency(),
				'pricingLow' => null,
				'pricingHigh' => null
            ];

            if (!is_null($lowPeriod) && $lowPeriod->getRate()->getRoughNightlyAmount() > 0) {
                $returnData['pricingLow'] = $lowPeriod->getRate()->getRoughNightlyAmount();
            }

            if (!is_null($highPeriod) && $highPeriod->getRate()->getRoughNightlyAmount() > 0) {
                $returnData['pricingHigh'] = $highPeriod->getRate()->getRoughNightlyAmount();
            }

            return $returnData;

        } catch (\Exception $ex) {
            throw new InvalidPricingConfigException("Could not transform pricing - ".$ex->getMessage(), 0, $ex);
        }
    }

}