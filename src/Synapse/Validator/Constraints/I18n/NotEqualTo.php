<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\NotEqualTo as ParentConstraint;

class NotEqualTo extends ParentConstraint
{
    public $message = 'MUST_NOT_BE_EQUAL_TO';

    public function validatedBy()
    {
        return parent::class + 'Validator';
    }
}
