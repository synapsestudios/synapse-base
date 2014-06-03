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

        $relatedEntity = $constraint->getMapper()->findById($id);

        // Invalid if related entity not found
        if (! $relatedEntity instanceof AbstractEntity) {
            return $this->addViolation($id, $constraint);
        }

        $relatedEntityArrayCopy = $relatedEntity->getArrayCopy();

        // Valid if related entity belongs to the main entity
        if ((int) Arr::get($relatedEntityArrayCopy, $constraint->getIdField()) === (int) $mainEntity->getId()) {
            return;
        }

        // Otherwise invalid
        $this->addViolation($id, $constraint);
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
