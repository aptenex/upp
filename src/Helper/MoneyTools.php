<?php

namespace Aptenex\Upp\Helper;

use Aptenex\Upp\Util\MoneyUtils;
use Aptenex\Upp\Parser\Structure\Operand;
use Aptenex\Upp\Parser\Structure\Rate;
use Money\Money;

class MoneyTools
{

    /**
     * @param Money $baseAmount
     * @param Money $operandAmount
     * @param string $operand
     *
     * @return Money
     */
    public static function applyMonetaryOperand(Money $baseAmount, Money $operandAmount, $operand)
    {
        switch ($operand) {

            case Operand::OP_ADDITION:
                $baseAmount = $baseAmount->add($operandAmount);
                break;

            case Operand::OP_SUBTRACTION:
                $baseAmount = $baseAmount->subtract($operandAmount);
                break;

            case Operand::OP_EQUALS:
                $baseAmount = $operandAmount;
                break;

        }

        return $baseAmount;
    }

    /**
     * @param       $amountOrPercentage
     * @param       $calculationMethod
     * @param Money $amountToBeCalculatedFrom
     *
     * @return Money
     */
    public static function applyCalculationMethodToAmount($amountOrPercentage, $calculationMethod, Money $amountToBeCalculatedFrom)
    {
        if ($calculationMethod === Rate::METHOD_FIXED) {
            return \Aptenex\Upp\Util\MoneyUtils::fromString($amountOrPercentage, $amountToBeCalculatedFrom->getCurrency());
        }

        return MoneyUtils::fromString(
            MoneyUtils::getConvertedAmount($amountToBeCalculatedFrom) * $amountOrPercentage,
            $amountToBeCalculatedFrom->getCurrency()
        );
    }

}