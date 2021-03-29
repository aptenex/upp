<?php

namespace Aptenex\Upp\Exception;

class Error
{

	const TYPE_EXCEEDS_MAX_OCCUPANCY = 'EXCEEDS_MAX_OCCUPANCY';
	const TYPE_EXCEEDS_MAX_STAY = 'EXCEEDS_MAX_STAY';
	const TYPE_MIN_STAY_NOT_MET = 'MIN_STAY_NOT_MET';
	const TYPE_CHANGE_OVER_DAY_MISMATCH = 'CHANGE_OVER_DAY_MISMATCH';
	const TYPE_START_DAY_MISMATCH = 'START_DAY_MISMATCH';
	const TYPE_END_DAY_MISMATCH = 'END_DAY_MISMATCH';
	const TYPE_MIN_ADVANCED_NOTICE_NOT_MET = 'MIN_ADVANCED_NOTICE_NOT_MET';
	const TYPE_STAY_NIGHT_INCREMENT_MISMATCH = 'STAY_NIGHT_INCREMENT_MISMATCH';
	const TYPE_OTHER = 'OTHER';
	const TYPE_NO_RATES_CONFIGURED = 'NO_RATES_CONFIGURED';
	const TYPE_UNKNOWN_PROPERTY = 'UNKNOWN_PROPERTY';
	const TYPE_PETS_NOT_ALLOWED = 'PETS_NOT_ALLOWED';

    const	TYPE_BILLING_ERROR_SCA_DECLINED	=	'BILLING_ERROR_SCA_DECLINED';
    const	TYPE_BILLING_ERROR_SCA_VERSION_NOT_SUPPORTED	=	'BILLING_ERROR_SCA_VERSION_NOT_SUPPORTED';
    const	TYPE_BILLING_ERROR_SCA_ERROR	=	'BILLING_ERROR_SCA_ERROR';
    const	TYPE_ACCEPTED_PAYMENT_METHOD_MISMATCH	=	'ACCEPTED_PAYMENT_METHOD_MISMATCH';
    const	TYPE_AGE_RESTRICTION	=	'AGE_RESTRICTION';
    const	TYPE_BILLING_ERROR	=	'BILLING_ERROR';
    const	TYPE_BILLING_ERROR_BILLING_ADDRESS_MISMATCH	=	'BILLING_ERROR_BILLING_ADDRESS_MISMATCH';
    const	TYPE_BILLING_ERROR_CC_DECLINED	=	'BILLING_ERROR_CC_DECLINED';
    const	TYPE_BILLING_ERROR_DEBIT_CARD_NOT_SUPPORTED	=	'BILLING_ERROR_DEBIT_CARD_NOT_SUPPORTED';
    const	TYPE_BILLING_ERROR_INSUFFICIENT_FUNDS	=	'BILLING_ERROR_INSUFFICIENT_FUNDS';
    const	TYPE_BILLING_ERROR_INVALID_BILLING_ADDRESS	=	'BILLING_ERROR_INVALID_BILLING_ADDRESS';
    const	TYPE_BILLING_ERROR_INVALID_CC_NUMBER	=	'BILLING_ERROR_INVALID_CC_NUMBER';
    const	TYPE_BILLING_ERROR_INVALID_CVV_CODE	=	'BILLING_ERROR_INVALID_CVV_CODE';
    const	TYPE_BILLING_ERROR_INVALID_EXPIRATION_DATE	=	'BILLING_ERROR_INVALID_EXPIRATION_DATE';
    const	TYPE_BILLING_ERROR_NO_CREDIT_CARD_CONFIGURED	=	'BILLING_ERROR_NO_CREDIT_CARD_CONFIGURED';
    const	TYPE_HTTP_OPERATION_FAILED	=	'HTTP_OPERATION_FAILED';
    const	TYPE_INSUFFICIENT_PAYMENT_METHOD_INFORMATION	=	'INSUFFICIENT_PAYMENT_METHOD_INFORMATION';
    const	TYPE_INVALID_PAYMENT_METHOD	=	'INVALID_PAYMENT_METHOD';
    const	TYPE_MERCHANT_ACCOUNT_ERROR	=	'MERCHANT_ACCOUNT_ERROR';
    const	TYPE_NO_ACCEPTED_PAYMENT_METHODS	=	'NO_ACCEPTED_PAYMENT_METHODS';
    const	TYPE_NO_TRAVELER_EMAIL_FOR_INQUIRY	=	'NO_TRAVELER_EMAIL_FOR_INQUIRY';
    const	TYPE_OLB_PROVIDER_NOT_PROVISIONED	=	'OLB_PROVIDER_NOT_PROVISIONED';
    const	TYPE_PROPERTY_NOT_AVAILABLE	=	'PROPERTY_NOT_AVAILABLE';
    const	TYPE_QUOTE_CURRENCY_MISMATCH	=	'QUOTE_CURRENCY_MISMATCH';
    const	TYPE_QUOTE_PRICE_MISMATCH	=	'QUOTE_PRICE_MISMATCH';
    const	TYPE_SERVER_ERROR	=	'SERVER_ERROR';
    const	TYPE_SERVICE_UNAVAILABLE	=	'SERVICE_UNAVAILABLE';
    const	TYPE_STAY_DATE_RECOMMENDATION	=	'STAY_DATE_RECOMMENDATION';
    const	TYPE_VALIDATION	=	'VALIDATION';
	
	private $type;
    private $unit = null;
    private $message;
    private $parameterKey = null;
    private $parameterUnit = null;
    private $internalMessage = null;

    /**
     * @param string $type
     * @param mixed|null $unit
     * @param string|null $internalMessage
     */
    public function __construct(string $type, $unit = null, string $internalMessage = null)
    {
	
		if (substr($type, 0, strlen('TYPE_')) === 'TYPE_') {
			$type = substr($type, strlen('TYPE_'));
		}
  
	
		if(!defined(sprintf('self::TYPE_%s', $type ) )){
			throw new \RuntimeException(sprintf("Error Type '%s' is not supported in " . __CLASS__ , $type));
		}
    	
        $this->type = $type;
        $this->unit = $unit;
        $this->internalMessage = $internalMessage;

        $errorData = ErrorHandler::ERROR_MAP[$type] ?? ErrorHandler::ERROR_MAP[ 'UNSPECIFIED_TEMPLATE'];
	
        $this->parameterKey = $errorData['parameterKey'];
        $this->parameterUnit = $errorData['parameterUnit'];
		if(self::TYPE_OTHER === $type){
			$this->message = $internalMessage;
		} elseif (is_null($this->unit)) {
            $this->message = $errorData['simple'];
        } else {
            $this->message = sprintf($errorData['parameterized'], $unit);
        }
       

    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return null
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @return bool
     */
    public function isParameterized(): bool
    {
        return !is_null($this->unit);
    }

    /**
     * @return null
     */
    public function getParameterKey()
    {
        return $this->parameterKey;
    }

    /**
     * @return null
     */
    public function getParameterUnit()
    {
        return $this->parameterUnit;
    }

    /**
     * @return null
     */
    public function getInternalMessage()
    {
        return $this->internalMessage;
    }
    
    public function getProcuroMessage(){
    	return $this->getInternalMessage();
	}

    /**
     * @return array
     */
    public function __toArray()
    {
        return [
            'type' => $this->getType(),
            'message' => $this->getMessage(),
            'unit' => $this->getUnit(),
            'parameterKey' => $this->getParameterKey(),
            'parameterUnit' => $this->getParameterUnit(),
            'internalMessage' => $this->getInternalMessage()
        ];
    }

    /**
     * @param array $data
     *
     * @return Error
     */
    public static function fromArrayData($data)
    {
        return new Error($data['type'], $data['unit'], $data['message']);
    }
    
    /**
     * @param mixed|null $unit
     */
    public function setUnit($unit): void
    {
        $this->unit = $unit;
    }
    
}