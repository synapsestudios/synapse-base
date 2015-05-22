<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Locale as ParentConstraint;

class Locale extends ParentConstraint
{
    public $message = 'INVALID_LOCALE';

    public function validatedBy()
    {
        return 'Symfony\Component\Validator\Constraints\LocaleValidator';
    }
}
