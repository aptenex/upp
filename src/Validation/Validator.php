<?php

namespace Aptenex\Upp\Validation;

use Aptenex\Upp\Context\PricingContext;
use Aptenex\Upp\Parser\Structure\PricingConfig;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

class Validator
{

    /**
     * @param PricingConfig $pricingConfig
     *
     * @return ValidationResult
     */
    public function validatePricingConfig(PricingConfig $pricingConfig)
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping(new AnnotationReader())
            ->getValidator()
        ;

        $errors = $validator->validate($pricingConfig);

        if ($errors->count() === 0) {
            return new ValidationResult(true, []);
        }

        return new ValidationResult(false, $this->getErrorArray($errors));
    }

    /**
     * @param PricingContext $pricingContext
     *
     * @return ValidationResult
     */
    public function validatePricingContext(PricingContext $pricingContext)
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping(new AnnotationReader())
            ->getValidator()
        ;

        $errors = $validator->validate($pricingContext);

        if ($errors->count() === 0) {
            return new ValidationResult(true, []);
        }

        return new ValidationResult(false, $this->getErrorArray($errors));
    }

    /**
     * List all errors of a given bound form
     *
     * @param ConstraintViolationListInterface $errorList
     *
     * @return array
     */
    private function getErrorArray(ConstraintViolationListInterface $errorList)
    {
        $errors = [];

        foreach ($errorList as $violation) {
            /** @var ConstraintViolation $violation */
            $errors[] = [
                'field'       => $violation->getPropertyPath(),
                'fieldPretty' => $this->prettyifyField($violation->getPropertyPath()),
                'message'     => $violation->getMessage()
            ];
        }

        return $errors;
    }

    /**
     * @param $string
     * @return string
     */
    private function prettyifyField($string)
    {
        $parts = explode('.', $string);

        $convertedParts = [];

        foreach($parts as $part) {
            $convertedParts[] = $this->fromCamelCaseToNormal($part);
        }

        return implode(' -> ', $convertedParts);
    }

    /**
     * @param $string
     * @return string
     */
    private function fromCamelCaseToNormal($string)
    {
        $parts = preg_match_all('/((?:^|[A-Z])[a-z]+)/', $string, $matches);

        if (empty($parts) || $parts == -1 || $parts == 0) {
            return '';
        }

        return ucwords(strtolower(implode(' ', $matches[0])));
    }

}