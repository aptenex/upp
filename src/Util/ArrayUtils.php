<?php

namespace Aptenex\Upp\Util;

use Aptenex\Upp\Helper\ArrayAccess;

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
     * @deprecated Use ArrayAccess::get() instead
     *
     * @param string $key
     * @param array $array
     * @param null $default
     *
     * @return array|mixed
     */
    public static function getNestedArrayValue($key, $array, $default = null)
    {
        return ArrayAccess::get($key, $array, $default);
    }

    /**
     * @deprecated Use ArrayAccess::has() instead
     *
     * @param string $key
     * @param array $array
     *
     * @return bool
     */
    public static function hasNestedArrayValue($key, $array)
    {
        return ArrayAccess::has($key, $array);
    }

    /**
     * @deprecated Use ArrayAccess::set() instead
     *
     * @param string $key
     * @param mixed $value
     * @param array $array
     *
     * @return array mixed
     */
    public static function setNestedArrayValue($key, $value, &$array)
    {
        return ArrayAccess::set($key, $value, $array);
    }

    /**
     * @deprecated Use ArrayAccess::remove() instead
     *
     * @param string|array $keys
     * @param array $array
     */
    public static function removeNestedArrayKey($keys, &$array)
    {
        ArrayAccess::remove($keys, $array);
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

                $value = self::get($rawDataField, $rawData);

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

                self::set($newFieldKey, $value, $nd);
            } else if (is_callable($rawDataField)) {
                $callbackResult = $rawDataField($rawData);
                self::set($newFieldKey, $callbackResult, $nd);
            } else if (is_array($rawDataField)) {
                // Gotta read config option
                switch ($rawDataField['type']) {
                    case 'string':
                        $default = null;
                        if (isset($rawDataField['default'])) {
                            $default = $rawDataField['default'];
                        }

                        $result = self::get($rawDataField['field'], $rawData, $default);

                        // Cast if not default
                        if ($result !== $default) {
                            $result = (string) $result;
                        }

                        self::set($newFieldKey, $result, $nd);
                        break;

                    case 'date':
                        $date = null;
                        $dateString = self::get($rawDataField['field'], $rawData);
                        if (DateUtils::isValidDate($dateString)) {
                            $date = (new \DateTime($dateString))->format("Y-m-d");
                        }

                        self::set($newFieldKey, $date, $nd);
                        break;
                        
                    case 'country':
                        $value = self::get($rawDataField['field'], $rawData);
                        
                        if (!is_null($value)) {
                            if (strlen($value) === 2) {
                                $value = strtoupper($value);
                            } else {
                                $reversedMap = array_flip(self::getCountryMapISO2ToName());
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
                        

                        self::set($newFieldKey, $value, $nd);
                        break;
                        
                    case 'int':
                        $default = null;
                        if (isset($rawDataField['default'])) {
                            $default = $rawDataField['default'];
                        }

                        $result = self::get($rawDataField['field'], $rawData, $default);

                        // Cast if not default
                        if ($result !== $default) {
                            $result = (int) $result;
                        }

                        self::set($newFieldKey, $result, $nd);
                        break;

                    case 'float':
                        $default = null;
                        if (isset($rawDataField['default'])) {
                            $default = $rawDataField['default'];
                        }

                        $result = self::get($rawDataField['field'], $rawData, $default);

                        // Cast if not default
                        if ($result !== $default) {
                            $result = (float) $result;
                        }

                        self::set($newFieldKey, $result, $nd);
                        break;

                    case 'custom':
                        $fieldData = null;
                        if (isset($rawDataField['field'])) {
                            $fieldData = self::get($rawDataField['field'], $rawData);
                        }

                        $callbackResult = $rawDataField['callback'](
                            $fieldData,
                            $rawData
                        );

                        self::set($newFieldKey, $callbackResult, $nd);
                        break;
                }

            } else {
                self::set($newFieldKey, null, $nd);
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
            "HKD" => 'HKD - Hong Kong Dollar',
            "MYR" => 'MYR - Malaysian Ringgit',
            "VND" => 'VND - Vietnamese Dong',
            "IDR" => 'IDR - Indonesian Rupiah',
            "PHP" => 'PHP - Philippine Peso',
            "SGD" => 'SGD - Singapore Dollar'
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

}