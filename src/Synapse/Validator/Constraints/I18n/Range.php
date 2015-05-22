<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Range as ParentConstraint;

class Range extends ParentConstraint
{
    public $minMessage     = 'BELOW_RANGE';
    public $maxMessage     = 'BEYOND_RANGE';
    public $invalidMessage = 'NOT_A_NUMBER';

    public function validatedBy()
    {
        return 'Symfony\Component\Validator\Constraints\RangeValidator';
    }
}
