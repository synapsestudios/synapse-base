<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Email as ParentConstraint;

class Email extends ParentConstraint
{
    public $message = 'INVALID_EMAIL';

    public function validatedBy()
    {
        return 'Symfony\Component\Validator\Constraints\EmailValidator';
    }
}
