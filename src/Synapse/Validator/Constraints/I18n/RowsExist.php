<?php

namespace Synapse\Validator\Constraints\I18n;

use Synapse\Validator\Constraints\I18n\RowExists as ParentConstraint;

class RowsExist extends ParentConstraint
{
    public function validatedBy()
    {
        return 'Synapse\Validator\Constraints\RowsExistValidator';
    }
}
