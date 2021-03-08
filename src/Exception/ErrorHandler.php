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
        Error::TYPE_BILLING_ERROR_SCA_DECLINED => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'We have been unable to complete Strong Customer Authentication for your payment method.',
            'parameterized' => null
        ],
        Error::TYPE_BILLING_ERROR_SCA_VERSION_NOT_SUPPORTED => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'We have been unable to complete Strong Customer Authentication for your payment method due to incompatible version as supplied by your card issuer. You must use a different payment method to proceed.',
            'parameterized' => null
        ],
        Error::TYPE_BILLING_ERROR_SCA_ERROR => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'We have been unable to complete Strong Customer Authentication for your payment method. You may try again in a few minutes or contact us directly',
            'parameterized' => null
        ],
        Error::TYPE_ACCEPTED_PAYMENT_METHOD_MISMATCH => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'Unable to complete your booking. Please try again in a few minutes.',
            'parameterized' => null
        ],
        Error::TYPE_AGE_RESTRICTION => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'Too many children selected',
            'parameterized' => null
        ],
        Error::TYPE_BILLING_ERROR => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'We\'re sorry, but the dates you are trying to reserve are no longer available. Please select new stay dates and try again.',
            'parameterized' => null
        ],
        Error::TYPE_BILLING_ERROR_BILLING_ADDRESS_MISMATCH => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'Please verify all information and try again.',
            'parameterized' => null
        ],
        Error::TYPE_BILLING_ERROR_CC_DECLINED => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'The credit card was declined. Please provide a different credit card.',
            'parameterized' => null
        ],
        Error::TYPE_BILLING_ERROR_DEBIT_CARD_NOT_SUPPORTED => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'We\'re sorry, debit cards are not supported. Please enter a valid payment method.',
            'parameterized' => null
        ],
        Error::TYPE_BILLING_ERROR_INSUFFICIENT_FUNDS => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'The credit card provided does not have sufficient funds to complete the transaction. Please provide a different credit card.',
            'parameterized' => null
        ],
        Error::TYPE_BILLING_ERROR_INVALID_BILLING_ADDRESS => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'The billing address is invalid.',
            'parameterized' => null
        ],
        Error::TYPE_BILLING_ERROR_INVALID_CC_NUMBER => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'The credit card number is invalid. Please provide a different credit card number.',
            'parameterized' => null
        ],
        Error::TYPE_BILLING_ERROR_INVALID_CVV_CODE => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'The CVV code is invalid. Please provide the correct CVV code for this credit card.',
            'parameterized' => null
        ],
        Error::TYPE_BILLING_ERROR_INVALID_EXPIRATION_DATE => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'The expiration date provided for this credit card is invalid. Please provide the correct expiration date for this credit card.',
            'parameterized' => null
        ],
        Error::TYPE_BILLING_ERROR_NO_CREDIT_CARD_CONFIGURED => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'A configuration error occurred for this property. Please select another property or contact the owner of this property.',
            'parameterized' => null
        ],

        Error::TYPE_HTTP_OPERATION_FAILED => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'We\'re sorry, we are unable to complete your booking. Please try again in a few minutes.',
            'parameterized' => null
        ],
        Error::TYPE_INSUFFICIENT_PAYMENT_METHOD_INFORMATION => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'Unable to complete your booking. Please try again in a few minutes.',
            'parameterized' => null
        ],
        Error::TYPE_INVALID_PAYMENT_METHOD => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'We\'re sorry, we are unable to complete your booking. Please try again in a few minutes.',
            'parameterized' => null
        ],
        Error::TYPE_MERCHANT_ACCOUNT_ERROR => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'Quote cannot be created at this time',
            'parameterized' => null
        ],

        Error::TYPE_NO_ACCEPTED_PAYMENT_METHODS => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'Quote cannot be created at this time',
            'parameterized' => null
        ],
        Error::TYPE_NO_RATES_CONFIGURED => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'Oops... Unfortunately a quote cannot be created at this time. Submit an inquiry and a quote will be emailed to you.',
            'parameterized' => null
        ],
        Error::TYPE_NO_TRAVELER_EMAIL_FOR_INQUIRY => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'Quote cannot be created at this time',
            'parameterized' => null
        ],
        Error::TYPE_OLB_PROVIDER_NOT_PROVISIONED => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'Quote cannot be created at this time',
            'parameterized' => null
        ],

        Error::TYPE_PETS_NOT_ALLOWED => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'Pets are not allowed',
            'parameterized' => null
        ],
        Error::TYPE_PROPERTY_NOT_AVAILABLE => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'We\'re sorry, but the dates you are trying to reserve are no longer available. Please select new stay dates and try again.',
            'parameterized' => null
        ],
        Error::TYPE_QUOTE_CURRENCY_MISMATCH => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'Unable to complete your booking. Please try again in a few minutes.',
            'parameterized' => null
        ],
        Error::TYPE_QUOTE_PRICE_MISMATCH => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'We\'re sorry, but the rates for this stay have changed. Please review the updated quote before resubmitting your payment.',
            'parameterized' => null
        ],
        Error::TYPE_SERVER_ERROR => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'We\'re sorry, we are unable to complete your booking. Please try again in a few minutes.',
            'parameterized' => null
        ],
        Error::TYPE_SERVICE_UNAVAILABLE => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'Quote cannot be created at this time',
            'parameterized' => null
        ],
        Error::TYPE_STAY_DATE_RECOMMENDATION => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'Quote cannot be created at this time',
            'parameterized' => null
        ],
        Error::TYPE_VALIDATION => [
            'parameterKey' => null,
            'parameterCount' => null,
            'simple' => 'We\'re sorry, we are unable to complete your booking. Please try again in a few minutes.',
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
            'simple' => 'We\'re sorry, we are unable to complete your booking. Please try again in a few minutes.',
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

        foreach ($this->getErrors() as $error) {
            $ta[] = $error->__toArray();
        }

        return $ta;
    }

}