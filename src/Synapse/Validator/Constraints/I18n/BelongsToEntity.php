<?php

namespace Synapse\Validator\Constraints\I18n;

use Synapse\Validator\Constraints\BelongsToEntity as ParentConstraint;

class BelongsToEntity extends ParentConstraint
{
    public $message = 'DOES_NOT_BELONG_TO_ENTITY';

    public function validatedBy()
    {
        return 'Synapse\Validator\COnstraints\BelongsToEntityValidator';
    }
}
