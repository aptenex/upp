<?php

namespace Aptenex\Upp\Exception;

class ErrorHandler
{

    const TYPE_EXCEEDS_MAX_OCCUPANCY = 'EXCEEDS_MAX_OCCUPANCY';
    const TYPE_MIN_STAY_NOT_MET = 'MIN_STAY_NOT_MET';
    const TYPE_CHANGE_OVER_DAY_MISMATCH = 'CHANGE_OVER_DAY_MISMATCH';
    const TYPE_START_DAY_MISMATCH = 'START_DAY_MISMATCH';
    const TYPE_END_DAY_MISMATCH = 'END_DAY_MISMATCH';
    const TYPE_MIN_ADVANCED_NOTICE_NOT_MET = 'MIN_ADVANCED_NOTICE_NOT_MET';
    const TYPE_STAY_NIGHT_INCREMENT_MISMATCH = 'STAY_NIGHT_INCREMENT_MISMATCH';

    const ERROR_MAP = [
        self::TYPE_EXCEEDS_MAX_OCCUPANCY => [
            'parameterKey' => 'count',
            'parameterUnit' => 'Travelers',
            'simple' => 'This property cannot accommodate the number of travelers selected.',
            'parameterized' => 'This property can only accommodate %s travelers.'
        ],
        self::TYPE_MIN_STAY_NOT_MET => [
            'parameterKey' => 'count',
            'parameterUnit' => 'Days',
            'simple' => 'This property requires a longer stay.',
            'parameterized' => 'This property requires a minimum stay of %s days.'
        ],
        self::TYPE_CHANGE_OVER_DAY_MISMATCH => [
            'parameterKey' => 'dayOfWeek',
            'parameterUnit' => null,
            'simple' => 'This property requires your stay to begin and end on the same day of the week.',
            'parameterized' => 'This property requires your stay to begin and end on a %s.'
        ],
        self::TYPE_START_DAY_MISMATCH => [
            'parameterKey' => 'dayOfWeek',
            'parameterUnit' => null,
            'simple' => 'This property requires your stay to begin on a different day.',
            'parameterized' => 'This property requires your stay to begin on a %s.'
        ],
        self::TYPE_END_DAY_MISMATCH => [
            'parameterKey' => 'dayOfWeek',
            'parameterUnit' => null,
            'simple' => 'This property requires your stay to end on a different day.',
            'parameterized' => 'This property requires your stay to end on a %s.'
        ],
        self::TYPE_MIN_ADVANCED_NOTICE_NOT_MET => [
            'parameterKey' => 'count',
            'parameterUnit' => 'Days',
            'simple' => 'This property requires more advance notice to book.',
            'parameterized' => 'This property requires %s days advance notice to book.'
        ],
        self::TYPE_STAY_NIGHT_INCREMENT_MISMATCH => [
            'parameterKey' => 'count',
            'parameterUnit' => 'Days',
            'simple' => 'This property requires stays to be booked in specific increments.',
            'parameterized' => 'This property requires stays to be booked in increments of "x" days.'
        ]
    ];

    private $errors = [];

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * @param Error $error
     */
    public function addError(Error $error)
    {
        $this->errors[] = $error;
    }

    /**
     * @param string $type
     * @param null|mixed $unit
     * @param string|null $procuroMessage
     */
    public function addErrorFromRaw($type, $unit = null, string $procuroMessage = null)
    {
        $this->addError(new Error($type, $unit, $procuroMessage));
    }

    /**
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        $ta = [];

        foreach($this->getErrors() as $error) {
            $ta[] = $error->__toArray();
        }

        return $ta;
    }

}