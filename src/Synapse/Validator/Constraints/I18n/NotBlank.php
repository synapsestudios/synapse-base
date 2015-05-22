<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\NotBlank as ParentConstraint;

class NotBlank extends ParentConstraint
{
    public $message = 'MUST_NOT_BE_BLANK';

    public function validatedBy()
    {
        return 'Symfony\Component\Validator\Constraints\NotBlankValidator';
    }
}
