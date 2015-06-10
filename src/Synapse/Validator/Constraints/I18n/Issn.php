<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Issn as ParentConstraint;

class Issn extends ParentConstraint
{
    public $message = 'INVALID_ISSN';

    public function validatedBy()
    {
        return parent::class + 'Validator';
    }
}
