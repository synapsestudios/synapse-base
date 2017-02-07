<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\NotIdenticalTo as ParentConstraint;

class NotIdenticalTo extends ParentConstraint
{
    public $message = 'MUST_NOT_BE_IDENTICAL_TO';

    public function validatedBy()
    {
        return parent::class + 'Validator';
    }
}
