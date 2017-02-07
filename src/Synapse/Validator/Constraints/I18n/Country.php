<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Country as ParentConstraint;

class Country extends ParentConstraint
{
    public $message = 'INVALID_COUNTRY';

    public function validatedBy()
    {
        return parent::class . 'Validator';
    }
}
