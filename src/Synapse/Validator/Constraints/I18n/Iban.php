<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Iban as ParentConstraint;

class Iban extends ParentConstraint
{
    public $message = 'INVALID_IBAN';

    public function validatedBy()
    {
        return parent::class + 'Validator';
    }
}
