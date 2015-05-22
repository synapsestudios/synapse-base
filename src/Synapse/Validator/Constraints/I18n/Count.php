<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Count as ParentConstraint;

class Count extends ParentConstraint
{
    public $minMessage   = 'COUNT_LESS_THAN_MIN';
    public $maxMessage   = 'COUNT_MORE_THAN_MAX';
    public $exactMessage = 'COUNT_NOT_EXACT';

    public function validatedBy()
    {
        return 'Symfony\Component\Validator\Constraints\CountValidator';
    }
}
