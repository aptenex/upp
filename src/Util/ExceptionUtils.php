<?php

namespace Aptenex\Upp\Util;

use Aptenex\Upp\Exception\BaseException;
use Aptenex\Upp\Models\Price;

class ExceptionUtils
{

    /**
     * @param $exception
     * @param Price $price
     * @param string $errorType
     * @param mixed|null $errorUnit
     */
    public static function handleErrorException(BaseException $exception, Price $price, $errorType, $errorUnit = null)
    {
        self::handleError($price, $errorType, $errorUnit, $exception->getMessage());
        self::handleException($exception, $price);
    }

    /**
     * @param Price $price
     * @param string $errorType
     * @param mixed|null $errorUnit
     * @param string|null $internalMessage
     */
    public static function handleError(Price $price, $errorType, $errorUnit = null, string $internalMessage = null)
    {
        $price->getErrors()->addErrorFromRaw($errorType, $errorUnit, $internalMessage);
    }

    /**
     * @param $exception
     * @param Price $price
     */
    public static function handleException($exception, Price $price)
    {
        $context = $price->getContextUsed();

        if (!$context->isForceGeneration()) {
            throw $exception; // Continue throwing the exception
        }
    }

}