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
	
		if (substr($type, 0, strlen('TYPE_')) == 'TYPE_') {
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
        return new Error($data['type'], $data['unit'], $data['internalMessage']);
    }

}