<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\LessThan as ParentConstraint;

class LessThan extends ParentConstraint
{
    public $message = 'MUST_BE_LESS_THAN';

    public function validatedBy()
    {
        return 'Symfony\Component\Validator\Constraints\LessThanValidator';
    }
}
