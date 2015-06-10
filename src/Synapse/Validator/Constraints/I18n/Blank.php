<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Blank as ParentConstraint;

class Blank extends ParentConstraint
{
    public $message = 'NOT_BLANK';

    public function validatedBy()
    {
        return parent::class . 'Validator';
    }
}
