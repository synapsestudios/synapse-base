<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\True as ParentConstraint;

class True extends ParentConstraint
{
    public $message = 'MUST_BE_TRUE';

    public function validatedBy()
    {
        return 'Symfony\Component\Validator\Constraints\TrueValidator';
    }
}
