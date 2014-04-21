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
     * Validate an associative array using the constraints defined in
     * getConstraints() and getOptionalConstraints().
     *
     * All constraints from getConstraints() are used.
     *
     * Constraints from getOptionalConstraints() are only used if the field exists in $values.
     *
     * @param  array                   $values Values to validate
     * @return ConstraintViolationList
     */
    public function validate(array $values)
    {
        $optionalConstraints = array_intersect_key(
            $this->getOptionalConstraints(),
            $values
        );

        $constraints = array_merge(
            $optionalConstraints,
            $this->getConstraints()
        );

        // Remove any fields that are not constrained
        $values = array_intersect_key($values, $constraints);

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
     *               objects sharing keys from the array being validated.
     */
    protected function getConstraints()
    {
        return [];
    }

    /**
     * Return an array of validation rules for optional fields
     *
     * This is necessary because there is no concept of optional fields in Symfony Validation
     *
     * @link http://silex.sensiolabs.org/doc/providers/validator.html#validating-associative-arrays
     * @return array Associative array of Symfony\Component\Validator\Constraints\*
     *               objects sharing keys from the array being validated.
     */
    protected function getOptionalConstraints()
    {
        return [];
    }
}
