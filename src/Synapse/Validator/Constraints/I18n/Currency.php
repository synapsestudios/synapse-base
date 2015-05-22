<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Currency as ParentConstraint;

class Currency extends ParentConstraint
{
    public $message = 'INVALID_CURRENCY';

    public function validatedBy()
    {
        return 'Symfony\Component\Validator\Constraints\CurrencyValidator';
    }
}
