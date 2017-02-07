<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\GreaterThanOrEqual as ParentConstraint;

class GreaterThanOrEqual extends ParentConstraint
{
    public $message = 'MUST_BE_GREATER_THAN_OR_EQUAL';

    public function validatedBy()
    {
        return parent::class + 'Validator';
    }
}
