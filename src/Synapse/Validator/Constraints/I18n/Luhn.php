<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Luhn as ParentConstraint;

class Luhn extends ParentConstraint
{
    public $message = 'INVALID_CARD_NUMBER';

    public function validatedBy()
    {
        return parent::class + 'Validator';
    }
}
