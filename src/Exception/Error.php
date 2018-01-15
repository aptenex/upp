<?php

namespace Aptenex\Upp\Exception;

class Error
{

    private $type;
    private $unit = null;
    private $message;
    private $parameterKey = null;
    private $parameterUnit = null;
    private $procuroMessage = null;

    /**
     * @param string $type
     * @param mixed|null $unit
     * @param string|null $procuroMessage
     */
    public function __construct(string $type, $unit = null, string $procuroMessage = null)
    {
        $this->type = $type;
        $this->unit = $unit;
        $this->procuroMessage = $procuroMessage;

        $errorData = ErrorHandler::ERROR_MAP[$type];

        $this->parameterKey = $errorData['parameterKey'];
        $this->parameterUnit = $errorData['parameterUnit'];

        if (is_null($this->unit)) {
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
    public function getProcuroMessage()
    {
        return $this->procuroMessage;
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
            'procuroMessage' => $this->getProcuroMessage()
        ];
    }

    /**
     * @param array $data
     *
     * @return Error
     */
    public static function fromArrayData($data)
    {
        return new Error($data['type'], $data['unit'], $data['procuroMessage']);
    }

}