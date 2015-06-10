<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Ip as ParentConstraint;

class Ip extends ParentConstraint
{
    public $message = 'INVALID_IP_ADDRESS';

    public function validatedBy()
    {
        return parent::class + 'Validator';
    }
}
