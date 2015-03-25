<?php

namespace Synapse\Validator;

trait ValidationErrorFormatterAwareTrait
{
    /**
     * @var ValidationErrorFormatter
     */
    protected $validationErrorFormatter;

    /**
     * Set the validation error formatter
     *
     * @param  ValidationErrorFormatter $formatter
     */
    public function setValidationErrorFormatter(ValidationErrorFormatter $formatter)
    {
        $this->validationErrorFormatter = $formatter;

        return $this;
    }
}
