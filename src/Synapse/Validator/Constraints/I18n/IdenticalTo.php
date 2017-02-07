<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\IdenticalTo as ParentConstraint;

class IdenticalTo extends ParentConstraint
{
    public $message = 'MUST_BE_IDENTICAL_TO';

    public function validatedBy()
    {
        return parent::class + 'Validator';
    }
}
