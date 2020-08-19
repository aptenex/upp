<?php

namespace Aptenex\Upp\Util;

class NumberUtils
{

    public static function clamp($value, $min, $max)
    {
        return $value > $max ? $max : $value < $min ? $min : $value;
    }

}