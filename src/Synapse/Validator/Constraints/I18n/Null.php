<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Null as ParentConstraint;

class Null extends ParentConstraint
{
    public $message = 'MUST_BE_NULL';

    public function validatedBy()
    {
        return 'Symfony\Component\Validator\Constraints\NullValidator';
    }
}
