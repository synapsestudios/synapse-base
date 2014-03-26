<?php

namespace Synapse\Entity;

use BadMethodCallException;
use InvalidArgumentException;
use Synapse\Stdlib\Arr;
use Zend\Stdlib\ArraySerializableInterface;

/**
 * An abstract class for representing database records as entity objects
 */
abstract class AbstractEntity implements ArraySerializableInterface
{
    /**
     * Entity data
     *
     * @var array
     */
    protected $object = [];

    /**
     * Handle magic getters and setters
     *
     * @param  string $method Name of the method called
     * @param  array $args    Arguments passed to the method
     * @return mixed
     */
    public function __call($method, array $args)
    {
        // If the method name is less than or equal to four characters
        // then it's not a getter or a setter
        if (strlen($method) <= 4) {
            throw new BadMethodCallException('Method not found');
        }

        // Whether we are setting or getting
        $type = substr($method, 0, 3);

        if ($type !== 'get' and $type !== 'set') {
            throw new BadMethodCallException('Method not found');
        }

        // Get the property name
        $property = lcfirst(substr($method, 3));

        $transform = function ($letters) {
            $letter = array_shift($letters);

            return '_' . strtolower($letter);
        };

        $property = preg_replace_callback('/([A-Z])/', $transform, $property);

        // Make sure the property exists
        if (! array_key_exists($property, $this->object)) {
            throw new InvalidArgumentException('Property, '.$property.', not found');
        }

        if ($type === 'get') {
            // Return the property
            return $this->object[$property];
        }

        // Not getting, so we must be setting; set the property
        $this->object[$property] = $args[0];

        // Fluent interface
        return $this;
    }

    /**
     * Get all columns of this entity
     *
     * @return array
     */
    public function getColumns()
    {
        return array_keys($this->object);
    }

    /**
     * Get values which are saved to the database.
     *
     * Useful if as_array is overridden to return values not
     * saved to the database.
     *
     * @return array
     */
    public function getDbValues()
    {
        return Arr::extract($this->asArray(), $this->getColumns());
    }

    /**
     * Return this entity as an array
     *
     * @return array
     */
    public function asArray()
    {
        return $this->object;
    }

    /**
     * Load object data in this entity from array
     *
     * @param  array  $values Entity data to be set
     * @return AbstractEntity
     */
    public function fromArray(array $values)
    {
        foreach ($this->object as $key => $value) {
            if (array_key_exists($key, $values)) {
                $setter = 'set'.ucfirst($key);
                $this->$setter($values[$key]);
            }
        }

        return $this;
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
     * {@inheritDoc}
     */
    public function exchangeArray(array $array)
    {
        return $this->fromArray($array);
    }

    /**
     * {@inheritDoc}
     */
    public function getArrayCopy()
    {
        return $this->asArray();
    }
}
