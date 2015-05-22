<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\LessThanOrEqual as ParentConstraint;

class LessThanOrEqual extends ParentConstraint
{
    public $message = 'MUST_BE_LESS_THAN_OR_EQUAL';

    public function validatedBy()
    {
        return 'Symfony\Component\Validator\Constraints\LessThanOrEqualValidator';
    }
}
