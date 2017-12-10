<?php

namespace Aptenex\Upp\Util;

class StringUtils
{

    public static function sanitizeStripe($string)
    {
        $replace = ['Stripe', 'stripe'];

        return str_replace($replace, 'VacayPay', $string);
    }

    /**
     * @param string $string
     *
     * @return bool|string
     */
    public static function getCapitalLetters($string)
    {
        if (preg_match_all('#([A-Z]+)#', $string, $matches)) {
            return implode('', $matches[1]);
        }

        return false;
    }

    public static function arrayToLines($array)
    {
        $l = [];

        $data = ArrayUtils::flattenArray($array);

        foreach($data as $key => $value) {
            if (is_null($value)) {
                $value = '*null';
            }

            if (is_bool($value)) {
                $value = $value ? '*true' : '*false';
            }

            $l[] = sprintf('%s: %s', $key, $value);
        }

        return implode(PHP_EOL, $l);
    }

    public static function canonicalize($string)
    {
        return str_replace(' ', '', null === $string ? null : mb_convert_case($string, MB_CASE_LOWER, mb_detect_encoding($string)));
    }

    public static function fromCamelCaseToUnderscore($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }

    public static function fromCamelCaseToNormal($string)
    {
        $parts = preg_match_all('/((?:^|[A-Z])[a-z]+)/', $string, $matches);

        if (empty($parts) || $parts == -1 || $parts == 0) {
            return '';
        }

        return ucwords(strtolower(implode(' ', $matches[0])));
    }

    public static function prettyifyField($string)
    {
        $parts = explode('.', $string);

        $convertedParts = [];

        foreach($parts as $part) {
            $convertedParts[] = self::fromCamelCaseToNormal($part);
        }

        return implode(' -> ', $convertedParts);
    }

    public static function prettifyStripeFormat($string)
    {
        return ucwords(str_replace('_', ' ', $string));
    }

    public static function createSimpleLink($url, $text, array $attributes = [])
    {
        return sprintf(
            '<a href="%s" %s>%s</a>',
            $url,
            self::renderHtmlAttributes($attributes),
            $text
        );
    }

    public static function renderHtmlAttributes(array $assocArray = [])
    {
        $flatAttributes = [];

        foreach ($assocArray as $key => $value) {
            $flatAttributes[] = sprintf('%s="%s"', $key, $value);
        }

        return implode(' ', $flatAttributes);
    }

    public static function truncate($value, $length = 30, $preserve = false, $separator = '...')
    {
        if (strlen($value) > $length) {
            if ($preserve) {
                if (false !== ($breakpoint = strpos($value, ' ', $length))) {
                    $length = $breakpoint;
                }
            }

            return rtrim(substr($value, 0, $length)) . $separator;
        }

        return $value;
    }

    public static function createJsConfirmLink($url, $text, array $attributes = [])
    {
        $class = isset($attributes['class']) ? $attributes['class'] : '';
        $class .= ' confirm-link';

        $attributes['class'] = $class;

        return sprintf(
            '<a data-href="%s" %s>%s</a>',
            $url,
            self::renderHtmlAttributes($attributes),
            $text
        );
    }

    public static function createBootstrapTableJsConfirmLink($url, $text, array $attributes = [])
    {
        $class = isset($attributes['class']) ? $attributes['class'] : '';
        $class .= ' confirm-link bs-table-ajax';

        $attributes['class'] = $class;

        return sprintf(
            '<a data-href="%s" %s>%s</a>',
            $url,
            self::renderHtmlAttributes($attributes),
            $text
        );
    }

    public static function toCamelCase($value){
        $value = ucwords(str_replace(array('-', '_'), ' ', $value));
        $value = str_replace(' ', '', $value);

        return lcfirst($value);
    }

    public static function seoUrl($string)
    {
        //Lower case everything
        $string = strtolower(trim($string));
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);

        return $string;
    }

    public static function dataFieldify($string)
    {
        //Lower case everything
        $string = strtolower(trim($string));
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^\.a-z0-9_\s-]/", "", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and dash to underscores
        $string = preg_replace("/[\s-]/", "_", $string);

        $string = str_replace('.', '_', $string);

        return $string;
    }

    public static function generateKey($string)
    {
        //Lower case everything
        $string = strtolower(trim($string));
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^\.a-z0-9_\s-]/", "", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);

        return $string;
    }

    public static function isTruthyString($string)
    {
        $truthyness = ['yes', '1', 1, 'true', true];

        if (in_array(trim(strtolower($string)), $truthyness, true)) {
            return true;
        }

        return false;
    }

    public static function boolToIcon($boolean, $inverse = false, $color = false)
    {
        if ($inverse) $boolean = !$boolean;

        if ($boolean) {
            return sprintf(
                '<i class="fa fa-lg fa-check-circle %s"></i>',
                $color ? 'text-success' : ''
            );
        }

        return sprintf(
            '<i class="fa fa-lg fa-times-circle %s"></i>',
            $color ? 'text-danger' : ''
        );
    }

    public static function boolToLabel($boolean, $inverse = false, $trueText = 'Enabled', $falseText = 'Disabled')
    {
        if ($inverse) $boolean = !$boolean;

        if ($boolean) {
            return sprintf('<span class="label label-success">%s</span>', $trueText);
        }

        return sprintf('<span class="label label-danger">%s</span>', $falseText);
    }

    public static function formatDotSeparatedString($string)
    {
        return trim(ucwords(strtolower(str_replace('.', ' ', $string))));
    }

    public static function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    public static function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }

    public static function convertToAscii($string)
    {
        // Replace Single Curly Quotes
        $search[] = chr(226) . chr(128) . chr(152);
        $replace[] = "'";
        $search[] = chr(226) . chr(128) . chr(153);
        $replace[] = "'";

        // Replace Smart Double Curly Quotes
        $search[] = chr(226) . chr(128) . chr(156);
        $replace[] = '"';
        $search[] = chr(226) . chr(128) . chr(157);
        $replace[] = '"';

        // Replace En Dash
        $search[] = chr(226) . chr(128) . chr(147);
        $replace[] = '--';

        // Replace Em Dash
        $search[] = chr(226) . chr(128) . chr(148);
        $replace[] = '---';

        // Replace Bullet
        $search[] = chr(226) . chr(128) . chr(162);
        $replace[] = '*';

        // Replace Middle Dot
        $search[] = chr(194) . chr(183);
        $replace[] = '*';

        // Replace Ellipsis with three consecutive dots
        $search[] = chr(226) . chr(128) . chr(166);
        $replace[] = '...';

        // Apply Replacements
        $string = str_replace($search, $replace, $string);

        // Remove any non-ASCII Characters
        $string = preg_replace("/[^\x01-\x7F]/", "", $string);

        return $string;
    }

}