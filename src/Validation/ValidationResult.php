<?php

namespace Aptenex\Upp\Validation;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationResult
{

    /**
     * @var bool
     */
    private $valid = false;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @param bool                                   $valid
     * @param array|ConstraintViolationListInterface $errors
     */
    public function __construct($valid, $errors)
    {
        $this->valid = $valid;

        foreach ($errors as $error) {
            $this->errors[] = $error;
        }
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->getErrors());
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

}