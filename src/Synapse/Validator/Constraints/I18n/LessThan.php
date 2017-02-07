<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\LessThan as ParentConstraint;

class LessThan extends ParentConstraint
{
    public $message = 'MUST_BE_LESS_THAN';

    public function validatedBy()
    {
        return parent::class + 'Validator';
    }
}
