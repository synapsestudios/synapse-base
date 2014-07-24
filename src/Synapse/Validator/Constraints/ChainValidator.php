<?php

namespace Synapse\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Group together constraints that will run in order but will stop when one fails.
 *
 * Copied from https://gist.github.com/rybakit/4705749 and altered slightly
 * because getPropertyPath() on the constraint violation object was returning
 * the field name duplicated e.g. exampleexample.
 */
class ChainValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $group = $this->context->getGroup();

        $violationList = $this->context->getViolations();
        $violationCountPrevious = $violationList->count();

        foreach ($constraint->constraints as $constr) {
            $this->context->validateValue($value, $constr, '', $group);

            if ($constraint->stopOnError && (count($violationList) !== $violationCountPrevious)) {
                return;
            }
        }
    }
}
