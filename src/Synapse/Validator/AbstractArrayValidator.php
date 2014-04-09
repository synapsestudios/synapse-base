<?php

namespace Synapse\Validator;

use Symfony\Component\Validator\Validator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Abstract class for validating arrays
 *
 * Simply extend this class and define getConstraints to create a concrete Validator
 */
abstract class AbstractArrayValidator
{
    /**
     * Symfony validator component
     *
     * @var Validator
     */
    protected $validator;

    /**
     * @param Validator $validator
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Validate an associative array using the constraints returned from getConstraints()
     *
     * @param  array                   $values Values to validate
     * @return ConstraintViolationList
     */
    public function validate(array $values)
    {
        $constraints     = $this->getConstraints();
        $arrayConstraint = new Assert\Collection($constraints);

        return $this->validator->validateValue(
            $values,
            $arrayConstraint
        );
    }

    /**
     * Return an array of validation rules for use with Symfony Validator
     *
     * @link http://silex.sensiolabs.org/doc/providers/validator.html#validating-associative-arrays
     * @return array Associative array of Symfony\Component\Validator\Constraints\*
     *               objects sharing keys from $this->object
     */
    abstract protected function getConstraints();
}
