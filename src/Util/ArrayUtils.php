<?php

namespace Aptenex\Upp\Util;

/**
 * Class ArrayUtils
 *
 * Some of these functions are taken / modified from https://github.com/illuminate/support/blob/master/Arr.php
 *
 * @package Aptenex\Upp\Util
 */

class ArrayUtils
{

    /**
     * This is not recursive on purpose
     *
     * @param $object
     * @return mixed
     */
    public static function convertStructureObjectToArray($object)
    {
        if (is_object($object) && method_exists($object, '__toArray')) {
            return $object->__toArray();
        }

        if (is_array($object)) {
            $items = [];

            foreach($object as $item) {
                if (is_object($item) && method_exists($item, '__toArray')) {
                    $items[] = $item->__toArray();
                } else {
                    $items[] = $item;
                }

            }

            return $items;
        }

        return $object;
    }

    /**
     * @param mixed $arrayLike
     *
     * @return array
     */
    public static function fuzzyToArray($arrayLike)
    {
        if (is_null($arrayLike) || is_string($arrayLike)) {
            return [];
        }

        if (is_array($arrayLike)) {
            return $arrayLike;
        }

        if (is_object($arrayLike)) {
            if (method_exists($arrayLike, 'toArray')) {
                return $arrayLike->toArray();
            }

            if (method_exists($arrayLike, '__toArray')) {
                return $arrayLike->__toArray();
            }
        }

        return [];
    }

    public static function cloneArray($array) {
        return array_map(function($element) {
            return ((is_array($element))
                ? call_user_func(__FUNCTION__, $element)
                : ((is_object($element))
                    ? clone $element
                    : $element
                )
            );
        }, $array);
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
    public static function getNestedArrayValue($key, $array, $default = null)
    {
        if (is_null($key)) return $array;

        if (isset($array[$key])) return $array[$key];

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }

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
    public static function hasNestedArrayValue($key, $array)
    {
        if (empty($array) || is_null($key)) return false;

        if (array_key_exists($key, $array)) return true;

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
     * @param mixed $value
     * @param array $array
     *
     * @return array mixed
     */
    public static function setNestedArrayValue($key, $value, &$array)
    {
        if (is_null($key)) return $array = $value;

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.

            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array =& $array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * @param string|array $keys
     * @param array $array
     */
    public static function removeNestedArrayKey($keys, &$array)
    {
        $original =& $array;

        foreach ((array) $keys as $key) {
            $parts = explode('.', $key);

            while (count($parts) > 1) {

                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array =& $array[$part];
                }

            }

            unset($array[array_shift($parts)]);

            // clean up after each pass
            $array =& $original;
        }
    }

    /**
     * @param string $column
     * @param array $entities
     *
     * @return array
     */
    public static function getEntityColumnArray($column, array $entities = [])
    {
        $ids = [];

        foreach ($entities as $entity) {

            $method = sprintf('get%s', ucfirst($column));

            if (method_exists($entity, $method)) {
                $ids[] = $entity->$method();
            }
        }

        return $ids;
    }

    public static function getFutureYears($yearsAhead = 5)
    {
        $current = (int) date("Y");

        $list = range($current, $current + $yearsAhead);

        return array_combine($list, $list);
    }

    /**
     * @param array $config
     * @param array $rawData
     * @param bool  $intelliCast When its a string parameter, try and cast to the correct data type
     *
     * @return array
     */
    public static function mapDataToNewFormat($rawData, $config, $intelliCast = true)
    {
        $nd = [];

        foreach($config as $newFieldKey => $rawDataField) {
            if (is_string($rawDataField)) {
                // See if this is piped
                $pipedParts = explode('|', $rawDataField);

                $rawDataField = $pipedParts[0];

                $value = ArrayUtils::getNestedArrayValue($rawDataField, $rawData);

                if (!is_null($value)) {
                    // Go through the rest of the piped parts and see if they are functions
                    foreach ($pipedParts as $index => $potentialFunction) {
                        if ($index === 0) {
                            continue;
                        }

                        if (function_exists($potentialFunction)) {
                            $value = $potentialFunction($value);
                        }
                    }
                }

                if ($intelliCast) {
                    if (is_integer($value)) {
                        $value = (int)$value;
                    } else if (is_float($value)) {
                        $value = (float)$value;
                    }
                }

                ArrayUtils::setNestedArrayValue($newFieldKey, $value, $nd);
            } else if (is_callable($rawDataField)) {
                $callbackResult = $rawDataField($rawData);
                ArrayUtils::setNestedArrayValue($newFieldKey, $callbackResult, $nd);
            } else if (is_array($rawDataField)) {
                // Gotta read config option
                switch ($rawDataField['type']) {
                    case 'string':
                        $default = null;
                        if (isset($rawDataField['default'])) {
                            $default = $rawDataField['default'];
                        }

                        $result = ArrayUtils::getNestedArrayValue($rawDataField['field'], $rawData, $default);

                        // Cast if not default
                        if ($result !== $default) {
                            $result = (string) $result;
                        }

                        ArrayUtils::setNestedArrayValue($newFieldKey, $result, $nd);
                        break;

                    case 'date':
                        $date = null;
                        $dateString = ArrayUtils::getNestedArrayValue($rawDataField['field'], $rawData);
                        if (DateUtils::isValidDate($dateString)) {
                            $date = (new \DateTime($dateString))->format("Y-m-d");
                        }

                        ArrayUtils::setNestedArrayValue($newFieldKey, $date, $nd);
                        break;
                        
                    case 'country':
                        $value = ArrayUtils::getNestedArrayValue($rawDataField['field'], $rawData);
                        
                        if (!is_null($value)) {
                            if (strlen($value) === 2) {
                                $value = strtoupper($value);
                            } else {
                                $reversedMap = array_flip(ArrayUtils::getCountryMapISO2ToName());
                                $countryName = ucwords($value);

                                if (strtolower($value) === 'usa') {

                                    $value = 'US';

                                } else {

                                    if (isset($reversedMap[$countryName])) {
                                        $value = $reversedMap[$countryName];
                                    }

                                }
                            }
                        }
                        

                        ArrayUtils::setNestedArrayValue($newFieldKey, $value, $nd);
                        break;
                        
                    case 'int':
                        $default = null;
                        if (isset($rawDataField['default'])) {
                            $default = $rawDataField['default'];
                        }

                        $result = ArrayUtils::getNestedArrayValue($rawDataField['field'], $rawData, $default);

                        // Cast if not default
                        if ($result !== $default) {
                            $result = (int) $result;
                        }

                        ArrayUtils::setNestedArrayValue($newFieldKey, $result, $nd);
                        break;

                    case 'float':
                        $default = null;
                        if (isset($rawDataField['default'])) {
                            $default = $rawDataField['default'];
                        }

                        $result = ArrayUtils::getNestedArrayValue($rawDataField['field'], $rawData, $default);

                        // Cast if not default
                        if ($result !== $default) {
                            $result = (float) $result;
                        }

                        ArrayUtils::setNestedArrayValue($newFieldKey, $result, $nd);
                        break;

                    case 'custom':
                        $fieldData = null;
                        if (isset($rawDataField['field'])) {
                            $fieldData = ArrayUtils::getNestedArrayValue($rawDataField['field'], $rawData);
                        }

                        $callbackResult = $rawDataField['callback'](
                            $fieldData,
                            $rawData
                        );

                        ArrayUtils::setNestedArrayValue($newFieldKey, $callbackResult, $nd);
                        break;
                }

            } else {
                ArrayUtils::setNestedArrayValue($newFieldKey, null, $nd);
            }
        }

        return $nd;
    }

    public static function getSupportedCountries()
    {
        return [
            'AU' => 'AU - Australia',
            'CA' => 'CA - Canada',
            'GB' => 'GB - United Kingdom',
            'US' => 'US - United States',
            'BE' => 'BE - Belgium',
            'DK' => 'DK - Denmark',
            'FI' => 'FI - Finland',
            'FR' => 'FR - France',
            'DE' => 'DE - Germany',
            'LU' => 'LU - Luxembourg',
            'NL' => 'NL - The Netherlands',
            'NO' => 'NO - Norway',
            'ES' => 'ES - Spain',
            'SE' => 'SE - Sweden',
            'AT' => 'AT - Austria',
            'IT' => 'IT - Italy',
            'IE' => 'IE - Ireland',
            'JP' => 'JP - Japan',
            'CH' => 'CH - Switzerland',
            'PT' => 'PT - Portugal',
            'HK' => 'HK - Hong Kong (Unsupported)'
        ];
    }

    public static function getSupportedCurrencies()
    {
        return [
            "AUD" => "AUD - Australian Dollar",
            "CAD" => "CAD - Canadian Dollar",
            "EUR" => "EUR - Euro",
            "GBP" => "GBP - British Pound",
            "USD" => "USD - United States Dollar",
            "JPY" => "JPY - Japanese Yen",
            "CHF" => "CHF - Swiss Franc",
            "THB" => 'THB - Thai Baht',
            "RUS" => 'RUS - Russian Ruble',
            "HKD" => 'HKD - Hong Kong Dollar'
        ];
    }

    public static function getCountryMapISO2ToName()
    {
        return [
            'AF' => 'Afghanistan',
            'AX' => 'Åland Islands',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'AS' => 'American Samoa',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AI' => 'Anguilla',
            'AQ' => 'Antarctica',
            'AG' => 'Antigua and Barbuda',
            'AR' => 'Argentina',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'AZ' => 'Azerbaijan',
            'BS' => 'Bahamas',
            'BH' => 'Bahrain',
            'BD' => 'Bangladesh',
            'BB' => 'Barbados',
            'BY' => 'Belarus',
            'BE' => 'Belgium',
            'BZ' => 'Belize',
            'BJ' => 'Benin',
            'BM' => 'Bermuda',
            'BT' => 'Bhutan',
            'BO' => 'Bolivia',
            'BA' => 'Bosnia and Herzegovina',
            'BW' => 'Botswana',
            'BV' => 'Bouvet Island',
            'BR' => 'Brazil',
            'IO' => 'British Indian Ocean Territory',
            'BN' => 'Brunei Darussalam',
            'BG' => 'Bulgaria',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'KH' => 'Cambodia',
            'CM' => 'Cameroon',
            'CA' => 'Canada',
            'CV' => 'Cape Verde',
            'KY' => 'Cayman Islands',
            'CF' => 'Central African Republic',
            'TD' => 'Chad',
            'CL' => 'Chile',
            'CN' => 'China',
            'CX' => 'Christmas Island',
            'CC' => 'Cocos (Keeling) Islands',
            'CO' => 'Colombia',
            'KM' => 'Comoros',
            'CG' => 'Congo',
            'CD' => 'Zaire',
            'CK' => 'Cook Islands',
            'CR' => 'Costa Rica',
            'CI' => 'Côte D\'Ivoire',
            'HR' => 'Croatia',
            'CU' => 'Cuba',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DK' => 'Denmark',
            'DJ' => 'Djibouti',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'EC' => 'Ecuador',
            'EG' => 'Egypt',
            'SV' => 'El Salvador',
            'GQ' => 'Equatorial Guinea',
            'ER' => 'Eritrea',
            'EE' => 'Estonia',
            'ET' => 'Ethiopia',
            'FK' => 'Falkland Islands (Malvinas)',
            'FO' => 'Faroe Islands',
            'FJ' => 'Fiji',
            'FI' => 'Finland',
            'FR' => 'France',
            'GF' => 'French Guiana',
            'PF' => 'French Polynesia',
            'TF' => 'French Southern Territories',
            'GA' => 'Gabon',
            'GM' => 'Gambia',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GR' => 'Greece',
            'GL' => 'Greenland',
            'GD' => 'Grenada',
            'GP' => 'Guadeloupe',
            'GU' => 'Guam',
            'GT' => 'Guatemala',
            'GG' => 'Guernsey',
            'GN' => 'Guinea',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HM' => 'Heard Island and Mcdonald Islands',
            'VA' => 'Vatican City State',
            'HN' => 'Honduras',
            'HK' => 'Hong Kong',
            'HU' => 'Hungary',
            'IS' => 'Iceland',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IR' => 'Iran, Islamic Republic of',
            'IQ' => 'Iraq',
            'IE' => 'Ireland',
            'IM' => 'Isle of Man',
            'IL' => 'Israel',
            'IT' => 'Italy',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JE' => 'Jersey',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'KENYA',
            'KI' => 'Kiribati',
            'KP' => 'Korea, Democratic People\'s Republic of',
            'KR' => 'Korea, Republic of',
            'KW' => 'Kuwait',
            'KG' => 'Kyrgyzstan',
            'LA' => 'Lao People\'s Democratic Republic',
            'LV' => 'Latvia',
            'LB' => 'Lebanon',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libyan Arab Jamahiriya',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MO' => 'Macao',
            'MK' => 'Macedonia, the Former Yugoslav Republic of',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MH' => 'Marshall Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius',
            'YT' => 'Mayotte',
            'MX' => 'Mexico',
            'FM' => 'Micronesia, Federated States of',
            'MD' => 'Moldova, Republic of',
            'MC' => 'Monaco',
            'MN' => 'Mongolia',
            'ME' => 'Montenegro',
            'MS' => 'Montserrat',
            'MA' => 'Morocco',
            'MZ' => 'Mozambique',
            'MM' => 'Myanmar',
            'NA' => 'Namibia',
            'NR' => 'Nauru',
            'NP' => 'Nepal',
            'NL' => 'Netherlands',
            'AN' => 'Netherlands Antilles',
            'NC' => 'New Caledonia',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'NU' => 'Niue',
            'NF' => 'Norfolk Island',
            'MP' => 'Northern Mariana Islands',
            'NO' => 'Norway',
            'OM' => 'Oman',
            'PK' => 'Pakistan',
            'PW' => 'Palau',
            'PS' => 'Palestinian Territory, Occupied',
            'PA' => 'Panama',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'PN' => 'Pitcairn',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'PR' => 'Puerto Rico',
            'QA' => 'Qatar',
            'RE' => 'Réunion',
            'RO' => 'Romania',
            'RU' => 'Russian Federation',
            'RW' => 'Rwanda',
            'SH' => 'Saint Helena',
            'KN' => 'Saint Kitts and Nevis',
            'LC' => 'Saint Lucia',
            'PM' => 'Saint Pierre and Miquelon',
            'VC' => 'Saint Vincent and the Grenadines',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'ST' => 'Sao Tome and Principe',
            'SA' => 'Saudi Arabia',
            'SN' => 'Senegal',
            'RS' => 'Serbia',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SG' => 'Singapore',
            'SK' => 'Slovakia',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia',
            'ZA' => 'South Africa',
            'GS' => 'South Georgia and the South Sandwich Islands',
            'ES' => 'Spain',
            'LK' => 'Sri Lanka',
            'SD' => 'Sudan',
            'SR' => 'Suriname',
            'SJ' => 'Svalbard and Jan Mayen',
            'SZ' => 'Swaziland',
            'SE' => 'Sweden',
            'CH' => 'Switzerland',
            'SY' => 'Syrian Arab Republic',
            'TW' => 'Taiwan, Province of China',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania, United Republic of',
            'TH' => 'Thailand',
            'TL' => 'Timor-Leste',
            'TG' => 'Togo',
            'TK' => 'Tokelau',
            'TO' => 'Tonga',
            'TT' => 'Trinidad and Tobago',
            'TN' => 'Tunisia',
            'TR' => 'Turkey',
            'TM' => 'Turkmenistan',
            'TC' => 'Turks and Caicos Islands',
            'TV' => 'Tuvalu',
            'UG' => 'Uganda',
            'UA' => 'Ukraine',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'US' => 'United States',
            'UM' => 'United States Minor Outlying Islands',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VE' => 'Venezuela',
            'VN' => 'Viet Nam',
            'VG' => 'Virgin Islands, British',
            'VI' => 'Virgin Islands, U.S.',
            'WF' => 'Wallis and Futuna',
            'EH' => 'Western Sahara',
            'YE' => 'Yemen',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
        ];
    }

    public static function getFontAwesomeIcons()
    {
        return [
            'fa-glass' => 'Glass',
            'fa-music' => 'Music',
            'fa-search' => 'Search',
            'fa-envelope-o' => 'Envelope O',
            'fa-heart' => 'Heart',
            'fa-star' => 'Star',
            'fa-star-o' => 'Star O',
            'fa-user' => 'User',
            'fa-film' => 'Film',
            'fa-th-large' => 'Th Large',
            'fa-th' => 'Th',
            'fa-th-list' => 'Th List',
            'fa-check' => 'Check',
            'fa-times' => 'Times',
            'fa-search-plus' => 'Search Plus',
            'fa-search-minus' => 'Search Minus',
            'fa-power-off' => 'Power Off',
            'fa-signal' => 'Signal',
            'fa-cog' => 'Cog',
            'fa-trash-o' => 'Trash O',
            'fa-home' => 'Home',
            'fa-file-o' => 'File O',
            'fa-clock-o' => 'Clock O',
            'fa-road' => 'Road',
            'fa-download' => 'Download',
            'fa-arrow-circle-o-down' => 'Arrow Circle O Down',
            'fa-arrow-circle-o-up' => 'Arrow Circle O Up',
            'fa-inbox' => 'Inbox',
            'fa-play-circle-o' => 'Play Circle O',
            'fa-repeat' => 'Repeat',
            'fa-refresh' => 'Refresh',
            'fa-list-alt' => 'List Alt',
            'fa-lock' => 'Lock',
            'fa-flag' => 'Flag',
            'fa-headphones' => 'Headphones',
            'fa-volume-off' => 'Volume Off',
            'fa-volume-down' => 'Volume Down',
            'fa-volume-up' => 'Volume Up',
            'fa-qrcode' => 'Qrcode',
            'fa-barcode' => 'Barcode',
            'fa-tag' => 'Tag',
            'fa-tags' => 'Tags',
            'fa-book' => 'Book',
            'fa-bookmark' => 'Bookmark',
            'fa-print' => 'Print',
            'fa-camera' => 'Camera',
            'fa-font' => 'Font',
            'fa-bold' => 'Bold',
            'fa-italic' => 'Italic',
            'fa-text-height' => 'Text Height',
            'fa-text-width' => 'Text Width',
            'fa-align-left' => 'Align Left',
            'fa-align-center' => 'Align Center',
            'fa-align-right' => 'Align Right',
            'fa-align-justify' => 'Align Justify',
            'fa-list' => 'List',
            'fa-outdent' => 'Outdent',
            'fa-indent' => 'Indent',
            'fa-video-camera' => 'Video Camera',
            'fa-picture-o' => 'Picture O',
            'fa-pencil' => 'Pencil',
            'fa-map-marker' => 'Map Marker',
            'fa-adjust' => 'Adjust',
            'fa-tint' => 'Tint',
            'fa-pencil-square-o' => 'Pencil Square O',
            'fa-share-square-o' => 'Share Square O',
            'fa-check-square-o' => 'Check Square O',
            'fa-arrows' => 'Arrows',
            'fa-step-backward' => 'Step Backward',
            'fa-fast-backward' => 'Fast Backward',
            'fa-backward' => 'Backward',
            'fa-play' => 'Play',
            'fa-pause' => 'Pause',
            'fa-stop' => 'Stop',
            'fa-forward' => 'Forward',
            'fa-fast-forward' => 'Fast Forward',
            'fa-step-forward' => 'Step Forward',
            'fa-eject' => 'Eject',
            'fa-chevron-left' => 'Chevron Left',
            'fa-chevron-right' => 'Chevron Right',
            'fa-plus-circle' => 'Plus Circle',
            'fa-minus-circle' => 'Minus Circle',
            'fa-times-circle' => 'Times Circle',
            'fa-check-circle' => 'Check Circle',
            'fa-question-circle' => 'Question Circle',
            'fa-info-circle' => 'Info Circle',
            'fa-crosshairs' => 'Crosshairs',
            'fa-times-circle-o' => 'Times Circle O',
            'fa-check-circle-o' => 'Check Circle O',
            'fa-ban' => 'Ban',
            'fa-arrow-left' => 'Arrow Left',
            'fa-arrow-right' => 'Arrow Right',
            'fa-arrow-up' => 'Arrow Up',
            'fa-arrow-down' => 'Arrow Down',
            'fa-share' => 'Share',
            'fa-expand' => 'Expand',
            'fa-compress' => 'Compress',
            'fa-plus' => 'Plus',
            'fa-minus' => 'Minus',
            'fa-asterisk' => 'Asterisk',
            'fa-exclamation-circle' => 'Exclamation Circle',
            'fa-gift' => 'Gift',
            'fa-leaf' => 'Leaf',
            'fa-fire' => 'Fire',
            'fa-eye' => 'Eye',
            'fa-eye-slash' => 'Eye Slash',
            'fa-exclamation-triangle' => 'Exclamation Triangle',
            'fa-plane' => 'Plane',
            'fa-calendar' => 'Calendar',
            'fa-random' => 'Random',
            'fa-comment' => 'Comment',
            'fa-magnet' => 'Magnet',
            'fa-chevron-up' => 'Chevron Up',
            'fa-chevron-down' => 'Chevron Down',
            'fa-retweet' => 'Retweet',
            'fa-shopping-cart' => 'Shopping Cart',
            'fa-folder' => 'Folder',
            'fa-folder-open' => 'Folder Open',
            'fa-arrows-v' => 'Arrows V',
            'fa-arrows-h' => 'Arrows H',
            'fa-bar-chart-o' => 'Bar Chart O',
            'fa-twitter-square' => 'Twitter Square',
            'fa-facebook-square' => 'Facebook Square',
            'fa-camera-retro' => 'Camera Retro',
            'fa-key' => 'Key',
            'fa-cogs' => 'Cogs',
            'fa-comments' => 'Comments',
            'fa-thumbs-o-up' => 'Thumbs O Up',
            'fa-thumbs-o-down' => 'Thumbs O Down',
            'fa-star-half' => 'Star Half',
            'fa-heart-o' => 'Heart O',
            'fa-sign-out' => 'Sign Out',
            'fa-linkedin-square' => 'Linkedin Square',
            'fa-thumb-tack' => 'Thumb Tack',
            'fa-external-link' => 'External Link',
            'fa-sign-in' => 'Sign In',
            'fa-trophy' => 'Trophy',
            'fa-github-square' => 'Github Square',
            'fa-upload' => 'Upload',
            'fa-lemon-o' => 'Lemon O',
            'fa-phone' => 'Phone',
            'fa-square-o' => 'Square O',
            'fa-bookmark-o' => 'Bookmark O',
            'fa-phone-square' => 'Phone Square',
            'fa-twitter' => 'Twitter',
            'fa-facebook' => 'Facebook',
            'fa-github' => 'Github',
            'fa-unlock' => 'Unlock',
            'fa-credit-card' => 'Credit Card',
            'fa-rss' => 'Rss',
            'fa-hdd-o' => 'Hdd O',
            'fa-bullhorn' => 'Bullhorn',
            'fa-bell' => 'Bell',
            'fa-certificate' => 'Certificate',
            'fa-hand-o-right' => 'Hand O Right',
            'fa-hand-o-left' => 'Hand O Left',
            'fa-hand-o-up' => 'Hand O Up',
            'fa-hand-o-down' => 'Hand O Down',
            'fa-arrow-circle-left' => 'Arrow Circle Left',
            'fa-arrow-circle-right' => 'Arrow Circle Right',
            'fa-arrow-circle-up' => 'Arrow Circle Up',
            'fa-arrow-circle-down' => 'Arrow Circle Down',
            'fa-globe' => 'Globe',
            'fa-wrench' => 'Wrench',
            'fa-tasks' => 'Tasks',
            'fa-filter' => 'Filter',
            'fa-briefcase' => 'Briefcase',
            'fa-arrows-alt' => 'Arrows Alt',
            'fa-users' => 'Users',
            'fa-link' => 'Link',
            'fa-cloud' => 'Cloud',
            'fa-flask' => 'Flask',
            'fa-scissors' => 'Scissors',
            'fa-files-o' => 'Files O',
            'fa-paperclip' => 'Paperclip',
            'fa-floppy-o' => 'Floppy O',
            'fa-square' => 'Square',
            'fa-bars' => 'Bars',
            'fa-list-ul' => 'List Ul',
            'fa-list-ol' => 'List Ol',
            'fa-strikethrough' => 'Strikethrough',
            'fa-underline' => 'Underline',
            'fa-table' => 'Table',
            'fa-magic' => 'Magic',
            'fa-truck' => 'Truck',
            'fa-pinterest' => 'Pinterest',
            'fa-pinterest-square' => 'Pinterest Square',
            'fa-google-plus-square' => 'Google Plus Square',
            'fa-google-plus' => 'Google Plus',
            'fa-money' => 'Money',
            'fa-caret-down' => 'Caret Down',
            'fa-caret-up' => 'Caret Up',
            'fa-caret-left' => 'Caret Left',
            'fa-caret-right' => 'Caret Right',
            'fa-columns' => 'Columns',
            'fa-sort' => 'Sort',
            'fa-sort-desc' => 'Sort Desc',
            'fa-sort-asc' => 'Sort Asc',
            'fa-envelope' => 'Envelope',
            'fa-linkedin' => 'Linkedin',
            'fa-undo' => 'Undo',
            'fa-gavel' => 'Gavel',
            'fa-tachometer' => 'Tachometer',
            'fa-comment-o' => 'Comment O',
            'fa-comments-o' => 'Comments O',
            'fa-bolt' => 'Bolt',
            'fa-sitemap' => 'Sitemap',
            'fa-umbrella' => 'Umbrella',
            'fa-clipboard' => 'Clipboard',
            'fa-lightbulb-o' => 'Lightbulb O',
            'fa-exchange' => 'Exchange',
            'fa-cloud-download' => 'Cloud Download',
            'fa-cloud-upload' => 'Cloud Upload',
            'fa-user-md' => 'User Md',
            'fa-stethoscope' => 'Stethoscope',
            'fa-suitcase' => 'Suitcase',
            'fa-bell-o' => 'Bell O',
            'fa-coffee' => 'Coffee',
            'fa-cutlery' => 'Cutlery',
            'fa-file-text-o' => 'File Text O',
            'fa-building-o' => 'Building O',
            'fa-hospital-o' => 'Hospital O',
            'fa-ambulance' => 'Ambulance',
            'fa-medkit' => 'Medkit',
            'fa-fighter-jet' => 'Fighter Jet',
            'fa-beer' => 'Beer',
            'fa-h-square' => 'H Square',
            'fa-plus-square' => 'Plus Square',
            'fa-angle-double-left' => 'Angle Double Left',
            'fa-angle-double-right' => 'Angle Double Right',
            'fa-angle-double-up' => 'Angle Double Up',
            'fa-angle-double-down' => 'Angle Double Down',
            'fa-angle-left' => 'Angle Left',
            'fa-angle-right' => 'Angle Right',
            'fa-angle-up' => 'Angle Up',
            'fa-angle-down' => 'Angle Down',
            'fa-desktop' => 'Desktop',
            'fa-laptop' => 'Laptop',
            'fa-tablet' => 'Tablet',
            'fa-mobile' => 'Mobile',
            'fa-circle-o' => 'Circle O',
            'fa-quote-left' => 'Quote Left',
            'fa-quote-right' => 'Quote Right',
            'fa-spinner' => 'Spinner',
            'fa-circle' => 'Circle',
            'fa-reply' => 'Reply',
            'fa-github-alt' => 'Github Alt',
            'fa-folder-o' => 'Folder O',
            'fa-folder-open-o' => 'Folder Open O',
            'fa-smile-o' => 'Smile O',
            'fa-frown-o' => 'Frown O',
            'fa-meh-o' => 'Meh O',
            'fa-gamepad' => 'Gamepad',
            'fa-keyboard-o' => 'Keyboard O',
            'fa-flag-o' => 'Flag O',
            'fa-flag-checkered' => 'Flag Checkered',
            'fa-terminal' => 'Terminal',
            'fa-code' => 'Code',
            'fa-reply-all' => 'Reply All',
            'fa-star-half-o' => 'Star Half O',
            'fa-location-arrow' => 'Location Arrow',
            'fa-crop' => 'Crop',
            'fa-code-fork' => 'Code Fork',
            'fa-chain-broken' => 'Chain Broken',
            'fa-question' => 'Question',
            'fa-info' => 'Info',
            'fa-exclamation' => 'Exclamation',
            'fa-superscript' => 'Superscript',
            'fa-subscript' => 'Subscript',
            'fa-eraser' => 'Eraser',
            'fa-puzzle-piece' => 'Puzzle Piece',
            'fa-microphone' => 'Microphone',
            'fa-microphone-slash' => 'Microphone Slash',
            'fa-shield' => 'Shield',
            'fa-calendar-o' => 'Calendar O',
            'fa-fire-extinguisher' => 'Fire Extinguisher',
            'fa-rocket' => 'Rocket',
            'fa-maxcdn' => 'Maxcdn',
            'fa-chevron-circle-left' => 'Chevron Circle Left',
            'fa-chevron-circle-right' => 'Chevron Circle Right',
            'fa-chevron-circle-up' => 'Chevron Circle Up',
            'fa-chevron-circle-down' => 'Chevron Circle Down',
            'fa-html5' => 'Html5',
            'fa-css3' => 'Css3',
            'fa-anchor' => 'Anchor',
            'fa-unlock-alt' => 'Unlock Alt',
            'fa-bullseye' => 'Bullseye',
            'fa-ellipsis-h' => 'Ellipsis H',
            'fa-ellipsis-v' => 'Ellipsis V',
            'fa-rss-square' => 'Rss Square',
            'fa-play-circle' => 'Play Circle',
            'fa-ticket' => 'Ticket',
            'fa-minus-square' => 'Minus Square',
            'fa-minus-square-o' => 'Minus Square O',
            'fa-level-up' => 'Level Up',
            'fa-level-down' => 'Level Down',
            'fa-check-square' => 'Check Square',
            'fa-pencil-square' => 'Pencil Square',
            'fa-external-link-square' => 'External Link Square',
            'fa-share-square' => 'Share Square',
            'fa-compass' => 'Compass',
            'fa-caret-square-o-down' => 'Caret Square O Down',
            'fa-caret-square-o-up' => 'Caret Square O Up',
            'fa-caret-square-o-right' => 'Caret Square O Right',
            'fa-eur' => 'Eur',
            'fa-gbp' => 'Gbp',
            'fa-usd' => 'Usd',
            'fa-inr' => 'Inr',
            'fa-jpy' => 'Jpy',
            'fa-rub' => 'Rub',
            'fa-krw' => 'Krw',
            'fa-btc' => 'Btc',
            'fa-file' => 'File',
            'fa-file-text' => 'File Text',
            'fa-sort-alpha-asc' => 'Sort Alpha Asc',
            'fa-sort-alpha-desc' => 'Sort Alpha Desc',
            'fa-sort-amount-asc' => 'Sort Amount Asc',
            'fa-sort-amount-desc' => 'Sort Amount Desc',
            'fa-sort-numeric-asc' => 'Sort Numeric Asc',
            'fa-sort-numeric-desc' => 'Sort Numeric Desc',
            'fa-thumbs-up' => 'Thumbs Up',
            'fa-thumbs-down' => 'Thumbs Down',
            'fa-youtube-square' => 'Youtube Square',
            'fa-youtube' => 'Youtube',
            'fa-xing' => 'Xing',
            'fa-xing-square' => 'Xing Square',
            'fa-youtube-play' => 'Youtube Play',
            'fa-dropbox' => 'Dropbox',
            'fa-stack-overflow' => 'Stack Overflow',
            'fa-instagram' => 'Instagram',
            'fa-flickr' => 'Flickr',
            'fa-adn' => 'Adn',
            'fa-bitbucket' => 'Bitbucket',
            'fa-bitbucket-square' => 'Bitbucket Square',
            'fa-tumblr' => 'Tumblr',
            'fa-tumblr-square' => 'Tumblr Square',
            'fa-long-arrow-down' => 'Long Arrow Down',
            'fa-long-arrow-up' => 'Long Arrow Up',
            'fa-long-arrow-left' => 'Long Arrow Left',
            'fa-long-arrow-right' => 'Long Arrow Right',
            'fa-apple' => 'Apple',
            'fa-windows' => 'Windows',
            'fa-android' => 'Android',
            'fa-linux' => 'Linux',
            'fa-dribbble' => 'Dribbble',
            'fa-skype' => 'Skype',
            'fa-foursquare' => 'Foursquare',
            'fa-trello' => 'Trello',
            'fa-female' => 'Female',
            'fa-male' => 'Male',
            'fa-gittip' => 'Gittip',
            'fa-sun-o' => 'Sun O',
            'fa-moon-o' => 'Moon O',
            'fa-archive' => 'Archive',
            'fa-bug' => 'Bug',
            'fa-vk' => 'Vk',
            'fa-weibo' => 'Weibo',
            'fa-renren' => 'Renren',
            'fa-pagelines' => 'Pagelines',
            'fa-stack-exchange' => 'Stack Exchange',
            'fa-arrow-circle-o-right' => 'Arrow Circle O Right',
            'fa-arrow-circle-o-left' => 'Arrow Circle O Left',
            'fa-caret-square-o-left' => 'Caret Square O Left',
            'fa-dot-circle-o' => 'Dot Circle O',
            'fa-wheelchair' => 'Wheelchair',
            'fa-vimeo-square' => 'Vimeo Square',
            'fa-try' => 'Try',
            'fa-plus-square-o' => 'Plus Square O',
            'fa-space-shuttle' => 'Space Shuttle',
            'fa-slack' => 'Slack',
            'fa-envelope-square' => 'Envelope Square',
            'fa-wordpress' => 'Wordpress',
            'fa-openid' => 'Openid',
            'fa-university' => 'University',
            'fa-graduation-cap' => 'Graduation Cap',
            'fa-yahoo' => 'Yahoo',
            'fa-google' => 'Google',
            'fa-reddit' => 'Reddit',
            'fa-reddit-square' => 'Reddit Square',
            'fa-stumbleupon-circle' => 'Stumbleupon Circle',
            'fa-stumbleupon' => 'Stumbleupon',
            'fa-delicious' => 'Delicious',
            'fa-digg' => 'Digg',
            'fa-pied-piper' => 'Pied Piper',
            'fa-pied-piper-alt' => 'Pied Piper Alt',
            'fa-drupal' => 'Drupal',
            'fa-joomla' => 'Joomla',
            'fa-language' => 'Language',
            'fa-fax' => 'Fax',
            'fa-building' => 'Building',
            'fa-child' => 'Child',
            'fa-paw' => 'Paw',
            'fa-spoon' => 'Spoon',
            'fa-cube' => 'Cube',
            'fa-cubes' => 'Cubes',
            'fa-behance' => 'Behance',
            'fa-behance-square' => 'Behance Square',
            'fa-steam' => 'Steam',
            'fa-steam-square' => 'Steam Square',
            'fa-recycle' => 'Recycle',
            'fa-car' => 'Car',
            'fa-taxi' => 'Taxi',
            'fa-tree' => 'Tree',
            'fa-spotify' => 'Spotify',
            'fa-deviantart' => 'Deviantart',
            'fa-soundcloud' => 'Soundcloud',
            'fa-database' => 'Database',
            'fa-file-pdf-o' => 'File Pdf O',
            'fa-file-word-o' => 'File Word O',
            'fa-file-excel-o' => 'File Excel O',
            'fa-file-powerpoint-o' => 'File Powerpoint O',
            'fa-file-image-o' => 'File Image O',
            'fa-file-archive-o' => 'File Archive O',
            'fa-file-audio-o' => 'File Audio O',
            'fa-file-video-o' => 'File Video O',
            'fa-file-code-o' => 'File Code O',
            'fa-vine' => 'Vine',
            'fa-codepen' => 'Codepen',
            'fa-jsfiddle' => 'Jsfiddle',
            'fa-life-ring' => 'Life Ring',
            'fa-circle-o-notch' => 'Circle O Notch',
            'fa-rebel' => 'Rebel',
            'fa-empire' => 'Empire',
            'fa-git-square' => 'Git Square',
            'fa-git' => 'Git',
            'fa-hacker-news' => 'Hacker News',
            'fa-tencent-weibo' => 'Tencent Weibo',
            'fa-qq' => 'Qq',
            'fa-weixin' => 'Weixin',
            'fa-paper-plane' => 'Paper Plane',
            'fa-paper-plane-o' => 'Paper Plane O',
            'fa-history' => 'History',
            'fa-circle-thin' => 'Circle Thin',
            'fa-header' => 'Header',
            'fa-paragraph' => 'Paragraph',
            'fa-sliders' => 'Sliders',
            'fa-share-alt' => 'Share Alt',
            'fa-share-alt-square' => 'Share Alt Square',
            'fa-bomb' => 'Bomb',
        ];
    }

}