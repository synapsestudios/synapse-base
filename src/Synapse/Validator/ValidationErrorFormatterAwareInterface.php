<?php

namespace Synapse\Validator;

interface ValidationErrorFormatterAwareInterface
{
    /**
     * Set the validation error formatter
     *
     * @param  ValidationErrorFormatter $formatter
     */
    public function setValidationErrorFormatter(ValidationErrorFormatter $formatter);
}
