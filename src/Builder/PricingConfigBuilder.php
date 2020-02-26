<?php

namespace Aptenex\Upp\Builder;

use Builder\BuildResult;

class PricingConfigBuilder
{

    /**
     * @param array $propertyConfig
     * @param array $tagConfigs
     *
     * @return array
     */
    public function buildConfig(array $propertyConfig, array $tagConfigs): BuildResult
    {
        $finalConfig = $propertyConfig;

        $taxesMergedByCurrencyMap = [];
        $modifiersMergedByCurrencyMap = [];

        // Loop through currency configs
        foreach ($propertyConfig['data'] as $index => $cc) {

            // We need to append taxes and modifiers into each matching currency config
            foreach ($tagConfigs as $tConfig) {
                if ($tConfig['schema'] !== 'tag-pricing') {
                    continue;
                }

                foreach($tConfig['data'] as $tagCc) {
                    if ($tagCc['currency'] !== $cc['currency']) {
                        continue;
                    }

                    /*
                     * Taxes
                     */

                    if (!\is_array($finalConfig['data'][$index]['taxes'])) {
                        $finalConfig['data'][$index]['taxes'] = [];
                    }

                    $finalConfig['data'][$index]['taxes'] = \array_merge($finalConfig['data'][$index]['taxes'], $tagCc['taxes']);

                    if (!isset($taxesMergedByCurrencyMap[$tagCc['currency']])) {
                        $taxesMergedByCurrencyMap[$tagCc['currency']] = [];
                    }

                    $taxesMergedByCurrencyMap[$tagCc['currency']] = \array_merge(
                        $taxesMergedByCurrencyMap[$tagCc['currency']],
                        $tagCc['taxes']
                    );

                    /*
                     * Modifiers
                     */

                    if (!\is_array($finalConfig['data'][$index]['modifiers'])) {
                        $finalConfig['data'][$index]['modifiers'] = [];
                    }

                    $finalConfig['data'][$index]['modifiers'] = \array_merge($finalConfig['data'][$index]['modifiers'], $tagCc['modifiers']);

                    if (!isset($modifiersMergedByCurrencyMap[$tagCc['currency']])) {
                        $modifiersMergedByCurrencyMap[$tagCc['currency']] = [];
                    }

                    $modifiersMergedByCurrencyMap[$tagCc['currency']] = \array_merge(
                        $modifiersMergedByCurrencyMap[$tagCc['currency']],
                        $tagCc['modifiers']
                    );
                }
            }

        }

        return new BuildResult($finalConfig, $propertyConfig, $taxesMergedByCurrencyMap, $modifiersMergedByCurrencyMap);
    }

}