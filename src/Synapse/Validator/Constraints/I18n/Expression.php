<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Expression as ParentConstraint;

class Expression extends ParentConstraint
{
    public $message = 'INVALID';

    public function validatedBy()
    {
        return parent::class + 'Validator';
    }
}
