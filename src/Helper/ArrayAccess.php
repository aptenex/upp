<?php

namespace Aptenex\Upp\Helper;

use Aptenex\Upp\Exception\BaseException;

class ArrayAccess
{

    /**
     * Assigns a unique identifier to each element in the array
     *
     * @param array $array
     * @return array
     */
    public static function assignHiddenId($array)
    {
        $id = 1;

        foreach($array as $index => $item) {
            $array[$index]['_id'] = $id;
            $id++;
        }

        return $array;
    }

    /**
     * Associative friendly version to get the last element of an array (without popping)
     *
     * @param array $array
     * @return mixed|null
     */
    public static function getLastElement($array) {
        if (count($array) < 1) {
            return null;
        }

        $keys = array_keys($array);

        return $array[$keys[sizeof($keys) - 1]];
    }

    /**
     * @param object $obj
     * @return array
     */
    public static function objectToArray($obj)
    {

        if (is_object($obj)) {
            $obj = (array) $obj;
        }

        if (is_array($obj)) {
            $new = [];

            foreach ($obj as $key => $val) {
                $new[$key] = self::objectToArray($val);
            }
        } else {
            $new = $obj;
        }

        return $new;
    }

    /**
     * @param array $array
     * @param string $prefix
     *
     * @return array
     */
    public static function flattenArray(array $array, $prefix = '')
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, self::flattenArray($value, $prefix . $key . '.'));
            } else {
                $result[$prefix . $key] = $value;
            }
        }

        return $result;
    }

    /**
     * Will only return the elements from data that are within the given whitelist
     *
     * @param array $data
     * @param array $whitelist
     *
     * @return array
     */
    public static function filterByWhitelist(array $data, array $whitelist)
    {
        return array_intersect($data, $whitelist);
    }

    /**
     * @param string $key
     * @param array $array
     * @param null $default
     *
     * @return array|mixed
     */
    public static function get($key, $array, $default = null)
    {
        if ($key === null) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * @param $key
     * @param $array
     * @param $default
     * @param array $whitelist
     *
     * @return array|mixed
     */
    public static function getViaWhitelist($key, $array, $default, array $whitelist)
    {
        $value = self::get($key, $array, null);

        if (!in_array($value, $whitelist, true)) {
            return $default;
        }

        return $value;
    }

    /**
     * @param string $key
     * @param array $array
     * @param BaseException $exceptionClass
     *
     * @param string $exceptionMessage
     * @return array|mixed
     */
    public static function getOrException($key, $array, $exceptionClass = null, $exceptionMessage = 'Base Exception')
    {
        if (!self::has($key, $array)) {
            if (is_null($exceptionClass)) {
                $exceptionClass = BaseException::class;
            }

            throw new $exceptionClass($exceptionMessage);
        }

        if (isset($array[$key])) return $array[$key];

        foreach (explode('.', $key) as $segment) {
            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * @param string $key
     * @param array $array
     *
     * @return bool
     */
    public static function has($key, $array)
    {
        if (empty($array) || $key === null) {
            return false;
        }

        if (array_key_exists($key, $array)) {
            return true;
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return false;
            }

            $array = $array[$segment];
        }

        return true;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param array  $array
     *
     * @return mixed
     */
    public static function set($key, $value, &$array)
    {
        if ($key === null) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.

            if (!isset($array[$key]) || !is_array($array[$key])) {
                if ('[]' === $key) {
                    $array[] = [];
                } else {
                    $array[$key] = [];
                }
            }

            $array = &$array[$key];
        }

        // If the value is an array and the value contains dot notation
        // We should also attempt to expand on that. (But only to a depth of one).
        if (is_array($value)) {
            $keysOfValues = array_keys($value);
            $filter = array_filter($keysOfValues, static function ($item) {
                return false !== strpos($item, '.');
            });

            if (is_array($filter) && !empty($filter)) {
                foreach ($filter as $explode) {
                    self::set($explode, $value[$explode], $explodedInnerDot);
                    // $array[] = $explodedInnerDot;
                    $value = $explodedInnerDot + $value;
                    unset($value[$explode]);
                }
            }
        }

        $key = array_shift($keys);
        if ('[]' === $key) {
            $array[] = $value;
        } else {
            $array[$key] = $value;
        }

        return $array;
    }

    /**
     * @param string|array $keys
     * @param array $array
     */
    public static function remove($keys, &$array)
    {
        $original = &$array;

        foreach ((array) $keys as $key) {
            $parts = explode('.', $key);

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                }
            }

            unset($array[array_shift($parts)]);

            // clean up after each pass
            $array = &$original;
        }
    }

}