<?php

namespace Synapse\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Synapse\Entity\AbstractEntity;
use Synapse\Mapper\AbstractMapper;
use Synapse\Stdlib\Arr;
use LogicException;

/**
 * Check if an id value belongs to an entity that has a foreign key
 * relationship to another entity specified in the constructor.
 */
class BelongsToEntity extends Constraint
{
    public $message = 'Related entity must have property {{ id_field }} equal to {{ value }}';

    /**
     * @var AbstractMapper
     */
    protected $mapper;

    /**
     * THe name of the field in the entity found with $mapper
     * that should be equal to the id of $entity.
     *
     * @var string
     */
    protected $idField;

    /**
     * @var AbstractEntity
     */
    protected $entity;

    /**
     * @param mixed $options
     */
    public function __construct(AbstractMapper $mapper, AbstractEntity $entity = null, $options = null)
    {
        if (! method_exists($mapper, 'findById')) {
            $message = sprintf(
                'Mapper injected into %s must use FinderTrait',
                get_class($this)
            );

            throw new LogicException($message);
        }

        $this->mapper = $mapper;

        $this->entity = $entity;

        parent::__construct($options);
    }

    /**
     * {@inheritDoc}
     */
    public function getRequiredOptions()
    {
        return array('idField');
    }

    /**
     * @return AbstractMapper
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @return AbstractEntity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return string
     */
    public function getIdField()
    {
        return $this->idField;
    }
}
