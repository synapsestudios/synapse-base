<?php

namespace Synapse\Validator\Constraints;

/**
 * Validator constraint meant to ensure that a row exists with the given ID
 */
class RowNotExists extends RowExists
{
    public $message = 'Entity must not exist with {{ field }} field equal to {{ value }}.';
}
