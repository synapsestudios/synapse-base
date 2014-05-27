<?php

namespace Synapse\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Synapse\Entity\AbstractEntity;

/**
 * Validator constraint meant to ensure that a row exists with the given ID
 */
class RowsExistValidator extends RowExistsValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($ids, Constraint $constraint)
    {
        if (! is_array($ids)) {
            $ids = [$ids];
        }

        foreach ($ids as $id) {
            parent::validate($id, $constraint);
        }
    }
}
