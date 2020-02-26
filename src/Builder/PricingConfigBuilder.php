<?php

namespace Aptenex\Upp\Builder;

class PricingConfigBuilder
{

    /**
     * @param array $propertyConfig
     * @param array $tagConfigs
     *
     * @return array
     */
    public function buildConfig(array $propertyConfig, array $tagConfigs): array
    {
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

                    if (!\is_array($propertyConfig['data'][$index]['taxes'])) {
                        $propertyConfig['data'][$index]['taxes'] = [];
                    }

                    $propertyConfig['data'][$index]['taxes'] = \array_merge($propertyConfig['data'][$index]['taxes'], $tagCc['taxes']);

                    /*
                     * Modifiers
                     */

                    if (!\is_array($propertyConfig['data'][$index]['modifiers'])) {
                        $propertyConfig['data'][$index]['modifiers'] = [];
                    }

                    $propertyConfig['data'][$index]['modifiers'] = \array_merge($propertyConfig['data'][$index]['modifiers'], $tagCc['modifiers']);
                }
            }

        }

        return $propertyConfig;
    }

}