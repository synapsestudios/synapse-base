<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\GreaterThan as ParentConstraint;

class GreaterThan extends ParentConstraint
{
    public $message = 'MUST_BE_GREATER_THAN';

    public function validatedBy()
    {
        return parent::class + 'Validator';
    }
}
