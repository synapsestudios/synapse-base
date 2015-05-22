<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Type as ParentConstraint;

class Type extends ParentConstraint
{
    public $message = 'MUST_BE_TYPE';

    public function validatedBy()
    {
        return 'Symfony\Component\Validator\Constraints\TypeValidator';
    }
}
