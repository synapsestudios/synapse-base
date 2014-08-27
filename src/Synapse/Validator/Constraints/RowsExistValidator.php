<?php

namespace Synapse\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Synapse\Entity\AbstractEntity;

/**
 * Validator constraint meant to ensure that rows exist with the given value set in the given field.
 *
 * If no field specified, defaults to `id`.
 */
class RowsExistValidator extends RowExistsValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($values, Constraint $constraint)
    {
        if (! is_array($values)) {
            $values = [$values];
        }

        foreach ($values as $value) {
            parent::validate($value, $constraint);
        }
    }
}
