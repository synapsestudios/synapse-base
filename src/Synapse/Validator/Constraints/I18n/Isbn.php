<?php

namespace Synapse\Validator\Constraints\I18n;

use Symfony\Component\Validator\Constraints\Isbn as ParentConstraint;

class Isbn extends ParentConstraint
{
    public $isbn10Message   = 'INVALID_ISBN_10.';
    public $isbn13Message   = 'INVALID_ISBN_13';
    public $bothIsbnMessage = 'INVALID_ISBN';

    public function validatedBy()
    {
        return 'Symfony\Component\Validator\Constraints\IsbnValidator';
    }
}
