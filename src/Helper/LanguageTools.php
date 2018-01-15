<?php

namespace Aptenex\Upp\Helper;

use Symfony\Component\Translation\TranslatorInterface;

class LanguageTools
{

    /**
     * @var TranslatorInterface
     */
    public static $translator;

    /**
     * @param string|array $singleDayOrArray
     *
     * @return string|array
     */
    public static function translateDaysOfWeek($singleDayOrArray)
    {
        if (is_string($singleDayOrArray)) {
            return self::trans(strtoupper($singleDayOrArray));
        }

        if (is_array($singleDayOrArray)) {
            $trans = [];

            foreach($singleDayOrArray as $dow) {
                $trans[] = self::trans(strtoupper($dow));
            }

            return $trans;
        }

        return null;
    }

    /**
     * @param array  $list
     * @param bool   $prettyify
     * @param string $ifEmptyLangKey
     *
     * @return string
     */
    public static function humanReadableList(array $list, $prettyify = true, $ifEmptyLangKey = 'LIST_EMPTY')
    {
        $len = count($list);

        if ($len <= 0) {
            return self::trans($ifEmptyLangKey);
        }

        if ($len === 1) {
            return ucfirst($list[0]);
        }

        if ($len === 2) {
            return self::trans('LIST_ONE_OR_OTHER', [
                '%choiceOne%' => ucfirst($list[0]),
                '%choiceTwo%' => ucfirst($list[1])
            ]);
        }

        $choices = '';
        $lastChoice = '';

        foreach($list as $index => $item) {
            $item = $prettyify ? ucfirst($item) : $item;
            if ($index === ($len - 1)) {
                // last
                $lastChoice = $item;
            } else if  ($index === ($len - 2)) {
                // second last - no comma
                $choices .= $item . '';
            } else {
                $choices .= $item . ', ';
            }
        }

        return self::trans('LIST_OR_MULTIPLE', [
            '%choices%' => $choices,
            '%lastChoice%' => $lastChoice
        ]);
    }

    /**
     * @param string $key
     * @param array $params
     * @return string
     */
    public static function trans($key, $params = [])
    {
        return self::$translator->trans($key, $params, 'upp');
    }

    /**
     * @param string $key
     * @param int $count
     * @param array $params
     *
     * @return string
     */
    public static function transChoice($key, $count, $params = [])
    {
        return self::$translator->transChoice($key, (int) $count, $params, 'upp');
    }

}