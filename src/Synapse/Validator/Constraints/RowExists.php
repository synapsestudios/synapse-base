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
    public $message = 'Entity must exist.';

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
        if (! method_exists($mapper, 'findById')) {
            $message = sprintf(
                'Mapper injected in into %s must use FinderTrait',
                get_class($this)
            );

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
}
