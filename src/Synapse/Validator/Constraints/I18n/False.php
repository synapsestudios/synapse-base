<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\False as ParentConstraint;

class False extends ParentConstraint
{
    public $message = 'MUST_BE_FALSE';

    public function validatedBy()
    {
        return parent::class + 'Validator';
    }
}
