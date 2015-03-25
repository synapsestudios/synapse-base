<?php

namespace Synapse\Validator\Constraints;

/**
 * Validator constraint meant to ensure that a row exists with the given ID
 */
class RowNotExists extends RowExists
{
    /**
     * Message to use if we're using the 'field' option
     *
     * @var string
     */
    const FIELD_MESSAGE = 'Entity must not exist with {{ field }} field equal to {{ value }}.';

    /**
     * {@inheritdoc}
     */
    public $message = 'Entity must not exist with specified parameters.';
}
