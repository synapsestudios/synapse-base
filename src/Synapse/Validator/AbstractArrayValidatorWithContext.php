<?php

namespace Synapse\Validator;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator;
use Synapse\Entity\AbstractEntity;

/**
 * Validation class to use if one or more of the constraints require
 * context data and optionally a context entity.
 */
abstract class AbstractArrayValidatorWithContext
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
     * {@inheritdoc}
     *
     * @param  array          $values
     * @param  AbstractEntity $contextEntity
     * @return ConstraintViolationList
     */
    public function validate(array $values, AbstractEntity $contextEntity = null)
    {
        $constraints = $this->getConstraints($values, $contextEntity);

        $arrayConstraint = new Assert\Collection($constraints);

        return $this->validator->validateValue(
            $values,
            $arrayConstraint
        );
    }

    /**
     * {@inheritDoc}
     *
     * @param  array          $contextData    Context data that may be passed to a constraint if needed.
     * @param  AbstractEntity $contextEntity  The existing entity to which the validated values will be applied.
     * @return array Associative array of Symfony\Component\Validator\Constraints\*
     *               objects sharing keys from the array being validated.
     */
    abstract protected function getConstraints(array $contextData, AbstractEntity $contextEntity = null);
}
