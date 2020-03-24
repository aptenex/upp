<?php

namespace Calculation;
use Aptenex\Upp\Parser\Structure\PricingConfig;

/**
 * This will take in a "total" and a parsed pricing config and try and determine what extras make up this total
 * so they can be entered, this is required as a channel manager such as RU does not provide **ANY** information
 * in a machine readable format (only a cleaning amount in a notes field). They do not provide the extras that
 * make up the total at all.
 *
 * Class ReverseExtraCalculator
 * @package Calculation
 */
class ReverseExtraCalculator
{

    public function calculateReverse($total, PricingConfig $config)
    {

        // We need to figure out the total reverse percentage



    }

}