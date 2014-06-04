<?php

namespace Synapse\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Synapse\Entity\AbstractEntity;
use Synapse\Stdlib\Arr;

/**
 * Check if an id value belongs to an entity that has a foreign key
 * relationship to another entity specified in the constructor.
 */
class BelongsToEntityValidator extends ConstraintValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($id, Constraint $constraint)
    {
        $mainEntity = $constraint->getEntity();

        // Invalid if no main entity is set
        if (! $mainEntity) {
            return $this->addViolation($id, $constraint);
        }

        $relatedEntity = $constraint->getMapper()->findBy([
            'id'                      => $id,
            $constraint->getIdField() => $mainEntity->getId()
        ]);

        // Invalid if related entity not found
        if (! $relatedEntity instanceof AbstractEntity) {
            return $this->addViolation($id, $constraint);
        }

        // Otherwise valid
        return;
    }

    /**
     * Add violation with error message
     *
     * @param $value
     */
    protected function addViolation($value, Constraint $constraint)
    {
        $this->context->addViolation(
            $constraint->message,
            [
                '{{ id_field }}' => $constraint->getIdField(),
                '{{ value }}'    => $value
            ],
            $value
        );
    }
}
