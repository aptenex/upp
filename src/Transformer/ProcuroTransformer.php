<?php

namespace Aptenex\Upp\Transformer;

use Aptenex\QTransform\QTransform;
use Aptenex\Upp\Exception\InvalidPricingConfigException;
use Aptenex\Upp\Parser\Structure\CurrencyConfig;
use Aptenex\Upp\Parser\Structure\Defaults;
use Aptenex\Upp\Parser\Structure\Modifier;
use Aptenex\Upp\Parser\Structure\Period;
use Aptenex\Upp\Parser\Structure\PricingConfig;
use Aptenex\Upp\Parser\Structure\Rate;
use Aptenex\Upp\Parser\Structure\Tax;

/**
 * This transformer converts the pricing config back to its json format
 */
class ProcuroTransformer implements TransformerInterface
{

    /**
     * @param PricingConfig $config
     *
     * @return array
     * @throws InvalidPricingConfigException
     */
    public function transform(PricingConfig $config)
    {
        $d = [
            'name'    => $config->getName(),
            'schema'  => $config->getSchema(),
            'version' => $config->getVersion(),
            'meta'    => $config->getMeta(),
            'data'    => []
        ];

        try {
            $ccData = [];

            foreach ($config->getCurrencyConfigs() as $ccObj) {
                $ccData[] = [
                    'currency'  => $ccObj->getCurrency(),
                    'defaults'  => $this->transformDefaults($ccObj->getDefaults()),
                    'taxes'     => $this->transformTaxes($ccObj->getTaxes()),
                    'periods'   => $this->transformPeriods($ccObj->getPeriods()),
                    'modifiers' => $this->transformModifiers($ccObj->getModifiers())
                ];
            }

            $d['data'] = $ccData;

            return $d;
        } catch (\Exception $ex) {
            throw new InvalidPricingConfigException("Could not transform pricing - " . $ex->getMessage(), 0, $ex);
        }
    }

    /**
     * @param Defaults $defaults
     *
     * @return array
     */
    private function transformDefaults($defaults)
    {
        if (!$defaults instanceof Defaults) {
            return [];
        }

        $defaultData = $defaults->__toArray();

        $mappingData = array_combine(array_keys($defaultData), array_keys($defaultData));

        return (new QTransform())->transformToArray($defaultData, $mappingData);
    }

    /**
     * @param array $taxes
     *
     * @return array
     */
    private function transformTaxes($taxes)
    {
        if (empty($taxes)) {
            return [];
        }

        $t = [];

        foreach($taxes as $tax) {
            $t[] = $this->transformTax($tax);
        }

        return $t;
    }

    /**
     * @param Tax $tax
     *
     * @return array
     */
    private function transformTax($tax)
    {
        if (!$tax instanceof Tax || empty($tax)) {
            return [];
        }

        $taxData = $tax->__toArray();

        $mappingData = array_combine(array_keys($taxData), array_keys($taxData));

        return (new QTransform())->transformToArray($taxData, $mappingData);
    }

    /**
     * @param array $periods
     *
     * @return array
     */
    private function transformPeriods($periods)
    {
        if (empty($periods)) {
            return [];
        }

        $p = [];

        foreach($periods as $period) {
            $p[] = $this->transformPeriod($period);
        }

        return $p;
    }

    /**
     * @param Period $period
     *
     * @return array
     */
    private function transformPeriod($period)
    {
        if (!$period instanceof Period || empty($period)) {
            return [];
        }

        $periodData = $period->__toArray();

        $mappingData = array_combine(array_keys($periodData), array_keys($periodData));

        return (new QTransform())->transformToArray($periodData, $mappingData);
    }

    /**
     * @param array $modifiers
     *
     * @return array
     */
    private function transformModifiers($modifiers)
    {
        if (empty($modifiers)) {
            return [];
        }

        $m = [];

        foreach($modifiers as $modifier) {
            $m[] = $this->transformModifier($modifier);
        }

        return $m;
    }

    /**
     * @param Modifier $modifier
     *
     * @return array
     */
    private function transformModifier($modifier)
    {
        if (!$modifier instanceof Modifier || empty($modifier)) {
            return [];
        }

        $modifierData = $modifier->__toArray();

        $mappingData = array_combine(array_keys($modifierData), array_keys($modifierData));

        return (new QTransform())->transformToArray($modifierData, $mappingData);
    }


}