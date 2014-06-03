<?php

namespace Synapse\Validator;

use Symfony\Component\Validator\Validator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Constraints as Assert;
use Synapse\Entity\AbstractEntity;

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
     * @var array
     */
    protected $contextData;

    /**
     * @var AbstractEntity
     */
    protected $contextEntity;

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
        $constraints = $this->getConstraints();

        $arrayConstraint = new Assert\Collection($constraints);

        return $this->validator->validateValue(
            $values,
            $arrayConstraint
        );
    }

    /**
     * Set the data being validated so that it can be passed to
     * validators that need it.
     *
     * @param array $contextData
     */
    public function setContextData(array $contextData)
    {
        $this->contextData = $contextData;
    }

    /**
     * Set the entity that will be updated with the data being validated so
     * that it can be passed to validators that need it.
     *
     * @param AbstractEntity $contextEntity
     */
    public function setContextEntity(AbstractEntity $contextEntity)
    {
        $this->contextEntity = $contextEntity;
    }

    /**
     * Return an array of validation rules for use with Symfony Validator
     *
     * @link http://silex.sensiolabs.org/doc/providers/validator.html#validating-associative-arrays
     *
     * In order to make a field optional, simply use the Optional constraint.
     *
     * If a field should be optional, but should have constraints if it exists,
     * simply provide the constraints to the constructor of the Optional constraint as such:
     *
     *     'first_name' => new Assert\Optional(new Assert\NotBlank())
     *
     * To add multiple constraints, provide an array:
     *
     *     'first_name' => new Assert\Optional([
     *         new Assert\NotBlank(),
     *         new Assert\NotNull(),
     *     ])
     *
     * Alternatively, configuration options such as allowMissingFields and
     * allowExtraFields can be set in the constraints array.
     *
     * @link http://symfony.com/doc/current/reference/constraints/Collection.html#presence-and-absence-of-fields
     *
     * @return array Associative array of Symfony\Component\Validator\Constraints\*
     *               objects sharing keys from the array being validated.
     */
    abstract protected function getConstraints();
}
