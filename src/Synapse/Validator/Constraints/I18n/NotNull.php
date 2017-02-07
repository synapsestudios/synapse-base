<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\NotNull as ParentConstraint;

class NotNull extends ParentConstraint
{
    public $message = 'MUST_NOT_BE_NULL';

    public function validatedBy()
    {
        return parent::class + 'Validator';
    }
}
