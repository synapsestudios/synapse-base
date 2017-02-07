<?php

namespace Synapse\Validator\Constraints\I18n;

use Synapse\Validator\Constraints\RowExists as ParentConstraint;

class RowExists extends ParentConstraint
{
    public $message = 'ROW_DOES_NOT_EXIST';

    // Message to use if we're using the 'field' option
    const FIELD_MESSAGE = 'ROW_DOES_NOT_EXIST_WITH_FIELD_EQUAL_TO';

    public function validatedBy()
    {
        return 'Synapse\Validator\COnstraints\RowExistsValidator';
    }
}
