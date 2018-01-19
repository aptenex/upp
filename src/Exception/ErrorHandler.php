<?php

namespace Aptenex\Upp\Exception;

class ErrorHandler
{

    const ERROR_MAP = [
    	'UNSPECIFIED_TEMPLATE' => [
			'parameterKey' => null,
			'parameterUnit' => null,
			'simple' => null,
			'parameterized' => null
		],
        Error::TYPE_EXCEEDS_MAX_OCCUPANCY => [
            'parameterKey' => 'count',
            'parameterUnit' => 'Travelers',
            'simple' => 'This property cannot accommodate the number of travelers selected.',
            'parameterized' => 'This property can only accommodate %s travelers.'
        ],
		Error::TYPE_MIN_STAY_NOT_MET => [
            'parameterKey' => 'count',
            'parameterUnit' => 'Days',
            'simple' => 'This property requires a longer stay.',
            'parameterized' => 'This property requires a minimum stay of %s days.'
        ],
		Error::TYPE_CHANGE_OVER_DAY_MISMATCH => [
            'parameterKey' => 'dayOfWeek',
            'parameterUnit' => null,
            'simple' => 'This property requires your stay to begin and end on the same day of the week.',
            'parameterized' => 'This property requires your stay to begin and end on a %s.'
        ],
		Error::TYPE_START_DAY_MISMATCH => [
            'parameterKey' => 'dayOfWeek',
            'parameterUnit' => null,
            'simple' => 'This property requires your stay to begin on a different day.',
            'parameterized' => 'This property requires your stay to begin on a %s.'
        ],
		Error::TYPE_END_DAY_MISMATCH => [
            'parameterKey' => 'dayOfWeek',
            'parameterUnit' => null,
            'simple' => 'This property requires your stay to end on a different day.',
            'parameterized' => 'This property requires your stay to end on a %s.'
        ],
		Error::TYPE_MIN_ADVANCED_NOTICE_NOT_MET => [
            'parameterKey' => 'count',
            'parameterUnit' => 'Days',
            'simple' => 'This property requires more advance notice to book.',
            'parameterized' => 'This property requires %s days advance notice to book.'
        ],
		Error::TYPE_STAY_NIGHT_INCREMENT_MISMATCH => [
            'parameterKey' => 'count',
            'parameterUnit' => 'Days',
            'simple' => 'This property requires stays to be booked in specific increments.',
            'parameterized' => 'This property requires stays to be booked in increments of "x" days.'
        ],
		Error::TYPE_OTHER => [
			'parameterKey' => null,
			'parameterUnit' => null,
			'simple' => 'Unknown error',
			'parameterized' => null // When null, Error will use internalMessage if exists.
		],
		Error::TYPE_UNKNOWN_PROPERTY => [
			'parameterKey' => null,
			'parameterUnit' => null,
			'simple' => 'Property has been discontinued',
			'parameterized' => null // When null, Error will use internalMessage if exists.
		],
		Error::TYPE_EXCEEDS_MAX_STAY => [
			'parameterKey' => 'count',
			'parameterUnit' => 'Days',
			'simple' => 'This property has a maximum stay length.',
			'parameterized' => 'This property has a maximum stay %s days.'
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
     * @param string|null $internalMessage
     */
    public function addErrorFromRaw($type, $unit = null, string $internalMessage = null)
    {
        $this->addError(new Error($type, $unit, $internalMessage));
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