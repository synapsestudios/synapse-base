<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\EqualTo as ParentConstraint;

class EqualTo extends ParentConstraint
{
    public $message = 'MUST_BE_EQUAL_TO';

    public function validatedBy()
    {
        return 'Symfony\Component\Validator\Constraints\EqualToValidator';
    }
}
