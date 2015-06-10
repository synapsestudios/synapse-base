<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Uuid as ParentConstraint;

class Uuid extends ParentConstraint
{
    public $message = 'INVALID_UUID';

    public function validatedBy()
    {
        return parent::class + 'Validator';
    }
}
