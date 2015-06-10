<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Language as ParentConstraint;

class Language extends ParentConstraint
{
    public $message = 'INVALID_LANGUAGE';

    public function validatedBy()
    {
        return parent::class + 'Validator';
    }
}
