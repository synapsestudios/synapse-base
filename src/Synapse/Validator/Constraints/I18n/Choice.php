<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Choice as ParentConstraint;

class Choice extends ParentConstraint
{
    public $message         = 'NOT_A_VALID_CHOICE';
    public $multipleMessage = 'ONE_OR_MORE_INVALID_VALUES';
    public $minMessage      = 'LESS_THAN_MIN_CHOICES_SELECTED';
    public $maxMessage      = 'MORE_THAN_MAX_CHOICES_SELECTED';

    public function validatedBy()
    {
        return 'Symfony\Component\Validator\Constraints\ChoiceValidator';
    }
}
