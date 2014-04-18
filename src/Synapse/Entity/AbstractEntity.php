<?php

namespace Synapse\Entity;

use Synapse\Log\LoggerAwareInterface;
use Synapse\Log\LoggerAwareTrait;
use Synapse\Stdlib\Arr;
use Synapse\Stdlib\DataObject;

/**
 * An abstract class for representing database records as entity objects
 */
abstract class AbstractEntity extends DataObject implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Get all columns of this entity that correspond to database columns
     *
     * @return array
     */
    public function getColumns()
    {
        return array_keys($this->object);
    }

    /**
     * Get values which are saved to the database
     *
     * Useful if as_array is overridden to return values not
     * saved to the database.
     *
     * @return array
     */
    public function getDbValues()
    {
        return Arr::extract($this->getArrayCopy(), $this->getColumns());
    }

    /**
     * Determine if this entity is new (not yet persisted) by checking for existence of an ID
     *
     * @return boolean
     */
    public function isNew()
    {
        return $this->getId() ? false : true;
    }
}
