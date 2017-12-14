<?php

namespace Aptenex\Upp\Calculation\Pricing\Strategy;

use Aptenex\Upp\Calculation\ControlItem\ControlItemInterface;
use Aptenex\Upp\Calculation\FinalPrice;
use Aptenex\Upp\Context\PricingContext;

interface PriceAlterationInterface
{

    /**
     * @param PricingContext $context
     * @param ControlItemInterface $controlItem
     * @param FinalPrice $fp
     *
     * @return bool
     */
    public function canAlter(PricingContext $context, ControlItemInterface $controlItem, FinalPrice $fp);

    /**
     * @param PricingContext $context
     * @param ControlItemInterface $controlItem
     * @param FinalPrice $fp
     *
     * @return null
     */
    public function alterPrice(PricingContext $context, ControlItemInterface $controlItem, FinalPrice $fp);

    /**
     * This is used for any other strategy fields that COULD apply even if the brackets don't match
     *
     * WARNING: YOU NEED TO CHECK IF THE STRATEGY EXISTS
     *
     * @param PricingContext       $context
     * @param ControlItemInterface $controlItem
     * @param FinalPrice           $fp
     *
     * @return null
     */
    public function postAlter(PricingContext $context, ControlItemInterface $controlItem, FinalPrice $fp);

}