<?php

namespace Synapse\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Synapse\Entity\AbstractEntity;

/**
 * Validator constraint meant to ensure that a row exists with the given ID
 */
class RowExistsValidator extends ConstraintValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($id, Constraint $constraint)
    {
        $entity = $constraint->getMapper()->findById($id);

        if ($entity instanceof AbstractEntity) {
            return;
        }

        $this->context->addViolation(
            $constraint->message,
            [],
            $id
        );
    }
}
