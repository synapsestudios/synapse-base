<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\DateTime as ParentConstraint;

class DateTime extends ParentConstraint
{
    public $message = 'INVALID_DATETIME';

    public function validatedBy()
    {
        return 'Symfony\Component\Validator\Constraints\DateTimeValidator';
    }
}
