<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Url as ParentConstraint;

class Url extends ParentConstraint
{
    public $message = 'INVALID_URL';

    public function validatedBy()
    {
        return 'Symfony\Component\Validator\Constraints\UrlValidator';
    }
}
