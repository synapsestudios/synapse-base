<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Regex as ParentConstraint;

class Regex extends ParentConstraint
{
    public $message = 'INVALID_VALUE';

    public function validatedBy()
    {
        return parent::class + 'Validator';
    }
}
