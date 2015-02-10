<?php

namespace Synapse\Stdlib;

use BadMethodCallException;
use InvalidArgumentException;
use Zend\Stdlib\ArraySerializableInterface;

abstract class DataObject implements ArraySerializableInterface
{
    /**
     * Entity data
     *
     * @var array
     */
    protected $object = [];

    /**
     * @param array $data  Initial data
     */
    public function __construct(array $data = [])
    {
        $this->exchangeArray($data);
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
        $this->object[$property] = $args[0];

        // Fluent interface
        return $this;
    }

    /**
     * Load object data in this entity from array
     *
     * @param  array  $data Entity data to be set
     * @return AbstractEntity
     */
    public function exchangeArray(array $data)
    {
        foreach ($this->object as $key => $value) {
            if (array_key_exists($key, $data)) {
                $setter = $this->getSetter($key);
                $this->$setter($data[$key]);
            }
        }

        return $this;
    }

    /**
     * Return this entity as an array
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return $this->object;
    }

    /**
     * Return the method name for the setter of a given variable
     *
     * Example: Converts 'user_id' to 'setUserId'
     *
     * @param  string $key Key of the variable in $this->object
     * @return string
     */
    protected function getSetter($key)
    {
        $key = str_replace('_', ' ', $key);
        $key = ucwords($key);
        $key = str_replace(' ', '', $key);

        return 'set'.$key;
    }

    /**
     * Determine whether the method being called is a getter or setter
     *
     * @param  string $method Method being called
     * @return string
     */
    protected function getMagicMethodType($method)
    {
        // If the method name is less than or equal to four characters
        // then it's not a getter or a setter
        if (strlen($method) <= 3) {
            throw new BadMethodCallException('Method not found');
        }

        // Whether we are setting or getting
        $type = substr($method, 0, 3);

        if ($type !== 'get' and $type !== 'set') {
            throw new BadMethodCallException('Method not found');
        }

        return $type;
    }

    /**
     * Determine the property that is being set or get
     *
     * @param  string $method Method being called
     * @return string
     */
    protected function getMagicMethodProperty($method)
    {
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

        return $property;
    }

    /**
     * Set the given field with the given value typecasted as an integer
     *
     * @param string $field Field to set
     * @param mixed  $value Value to set
     */
    protected function setAsInteger($field, $value)
    {
        $this->object[$field] = (int) $value;
    }

    /**
     * Set the given field with the given value typecasted as a float
     *
     * @param string $field Field to set
     * @param mixed  $value Value to set
     */
    protected function setAsFloat($field, $value)
    {
        $this->object[$field] = (float) $value;
    }

    /**
     * Set the given field with the given value typecasted as a boolean
     *
     * @param string $field Field to set
     * @param mixed  $value Value to set
     */
    protected function setAsBoolean($field, $value)
    {
        $this->object[$field] = (bool) $value;
    }

    /**
     * Set the given field with the given value typecasted as a string
     *
     * @param string $field Field to set
     * @param mixed  $value Value to set
     */
    protected function setAsString($field, $value)
    {
        $this->object[$field] = (string) $value;
    }
}
