<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Date as ParentConstraint;

class Date extends ParentConstraint
{
    public $message = 'INVALID_DATE';

    public function validatedBy()
    {
        return parent::class . 'Validator';
    }
}
