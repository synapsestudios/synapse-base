<?php

namespace Synapse\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Synapse\Entity\AbstractEntity;

/**
 * Validator constraint meant to ensure that a row does not exists with the given value set in the given field.
 *
 * If no field specified, defaults to `id`.
 */
class RowNotExistsValidator extends ConstraintValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $entity = $constraint->getMapper()->findBy([
            $constraint->field => $value
        ]);

        if (! $entity instanceof AbstractEntity) {
            return;
        }

        $this->context->addViolation(
            $constraint->message,
            [
                '{{ field }}' => $constraint->field,
                '{{ value }}' => $value,
            ],
            $value
        );
    }
}
