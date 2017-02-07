<?php

namespace Synapse\Validator\Constraints\I18n;

use Synapse\Validator\Constraints\RowNotExists as ParentConstraint;

class RowNotExists extends ParentConstraint
{
    public $message = 'ROW_EXISTS';

    // Message to use if we're using the 'field' option
    const FIELD_MESSAGE = 'ROW_EXISTS_WITH_FIELD_EQUAL_TO';

    public function validatedBy()
    {
        return 'Synapse\Validator\COnstraints\RowNotExistsValidator';
    }
}
