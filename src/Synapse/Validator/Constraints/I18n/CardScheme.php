<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\CardScheme as ParentConstraint;

class CardScheme extends ParentConstraint
{
    public $message = 'UNSUPPORTED_CARD_TYPE_OR_INVALID_CARD_NUMBER';

    public function validatedBy()
    {
        return 'Symfony\Component\Validator\Constraints\CardSchemeValidator';
    }
}
