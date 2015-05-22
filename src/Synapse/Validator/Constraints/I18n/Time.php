<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Time as ParentConstraint;

class Time extends ParentConstraint
{
    public $message = 'INVALID_TIME';

    public function validatedBy()
    {
        return 'Symfony\Component\Validator\Constraints\TimeValidator';
    }
}
