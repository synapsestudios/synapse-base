<?php

namespace Synapse\Entity;

use Synapse\Log\LoggerAwareInterface;
use Synapse\Log\LoggerAwareTrait;
use Synapse\Stdlib\Arr;
use Synapse\Stdlib\DataObject;

/**
 * An abstract class for representing database records as entity objects
 */
abstract class AbstractEntity extends DataObject implements AbstractEntityInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $dirtyFields = [];

    /**
     * @param array $data  Initial data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->dirtyFields = [];
    }

    /**
     * Handle magic getters and setters
     *
     * @param  string $method Name of the method called
     * @param  array $args    Arguments passed to the method
     * @return mixed
     */
    public function __call($method, array $args)
    {
        $type     = $this->getMagicMethodType($method);
        $property = $this->getMagicMethodProperty($method);

        if ($type === 'get') {
            // Return the property
            return $this->object[$property];
        }

        // Not getting, so we must be setting; set the property
        if (array_key_exists($property, $this->object) && $this->object[$property] !== $args[0]) {
            $this->object[$property] = $args[0];
            if (in_array($property, $this->getColumns())) {
                $this->dirtyFields[$property] = true;
            }
        }

        // Fluent interface
        return $this;
    }

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
     * Returns only the changed values
     * @return array
     */
    public function getChangedDbValues()
    {
        return array_intersect_key($this->getDbValues(), $this->dirtyFields);
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

    /**
     * Set the given field with the given value typecasted as an integer
     *
     * @param string $field Field to set
     * @param mixed  $value Value to set
     *
     * @return $this
     */
    protected function setAsInteger($field, $value)
    {
        if (in_array($field, $this->getColumns()) && (
                !array_key_exists($field, $this->object) ||
                $this->object[$field] !== $value
        )) {
            $this->dirtyFields[$field] = true;
        }
        $this->object[$field] = (int) $value;

        return $this;
    }

    /**
     * Set the given field with the given value typecasted as a float
     *
     * @param string $field Field to set
     * @param mixed  $value Value to set
     *
     * @return $this
     */
    protected function setAsFloat($field, $value)
    {
        if (in_array($field, $this->getColumns()) && (
                !array_key_exists($field, $this->object) ||
                $this->object[$field] !== $value
            )) {
            $this->dirtyFields[$field] = true;
        }
        $this->object[$field] = (float) $value;

        return $this;
    }

    /**
     * Set the given field with the given value typecasted as a boolean
     *
     * @param string $field Field to set
     * @param mixed  $value Value to set
     *
     * @return $this
     */
    protected function setAsBoolean($field, $value)
    {
        if (in_array($field, $this->getColumns()) && (
                !array_key_exists($field, $this->object) ||
                $this->object[$field] !== $value
            )) {
            $this->dirtyFields[$field] = true;
        }
        $this->object[$field] = (bool) $value;

        return $this;
    }

    /**
     * Set the given field with the given value typecasted as a string
     *
     * @param string $field Field to set
     * @param mixed  $value Value to set
     *
     * @return $this
     */
    protected function setAsString($field, $value)
    {
        if (in_array($field, $this->getColumns()) && (
                !array_key_exists($field, $this->object) ||
                $this->object[$field] !== $value
            )) {
            $this->dirtyFields[$field] = true;
        }
        $this->object[$field] = (string) $value;

        return $this;
    }
}
