<?php

namespace Synapse\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Synapse\Mapper\AbstractMapper;
use LogicException;

/**
 * Validator constraint meant to ensure that a row exists with the given ID
 */
class RowExists extends Constraint
{
    public $message        = 'Entity must exist with specified parameters.';
    public $filterCallback = null;
    public $field          = 'id';

    /**
     * Message to use if we're using the 'field' option
     *
     * @var string
     */
    const FIELD_MESSAGE = 'Entity must exist with {{ field }} field equal to {{ value }}.';

    /**
     * Mapper to use to search for entity
     *
     * @var AbstractMapper
     */
    protected $mapper;

    /**
     * @param AbstractMapper $mapper
     * @param mixed          $options
     */
    public function __construct(AbstractMapper $mapper, $options = null)
    {
        if (isset($options['filterCallback']) && isset($options['field'])) {
            throw new LogicException('filterCallback and field are both set. Only one is expected.');
        }

        if (isset($options['field']) && (! isset($options['message']))) {
            $this->message = $self::FIELD_MESSAGE;
        }

        if (! method_exists($mapper, 'findBy')) {
            $message = sprintf(
                'Mapper injected into %s must use FinderTrait',
                get_class($this)
            );

            throw new LogicException($message);
        }

        if (isset($options['field']) && (! is_string($options['field']))) {
            $message = sprintf('RowExists field must be string, %s given', gettype($options['field']));

            throw new LogicException($message);
        }

        $this->mapper = $mapper;

        parent::__construct($options);
    }

    /**
     * @return AbstractMapper
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * Get callback that takes the value being validated and returns an array of
     * "wheres" to be used with the mapper.
     *
     * @return function
     */
    public function getFilterCallback()
    {
        if ($this->filterCallback !== null) {
            $callback = $this->filterCallback;
        } else {
            $field = $this->field;

            $callback = function ($value) use ($field) {
                return [$field => $value];
            };
        }

        return $callback;
    }
}
