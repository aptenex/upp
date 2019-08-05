<?php

namespace Aptenex\Upp\Util;

class TestUtils
{

    public static function getPriceTestByKey(array $priceConfigs, string $key)
    {
        $filtered = array_filter($priceConfigs, function ($item) use ($key) {
            if (isset($item['key']) && $item['key'] === $key) {
                return $item;
            }

            return false;
        });

        return array_shift($filtered);
    }

}