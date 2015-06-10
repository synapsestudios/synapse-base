<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Length as ParentConstraint;

class Length extends ParentConstraint
{
    public $maxMessage   = 'VALUE_TOO_LONG';
    public $minMessage   = 'VALUE_TOO_SHORT';
    public $exactMessage = 'VALUE_NOT_EXACT_LENGTH';

    public function validatedBy()
    {
        return parent::class + 'Validator';
    }
}
