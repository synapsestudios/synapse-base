<?php

namespace Synapse\Entity;

use Zend\Stdlib\ArraySerializableInterface;

interface AbstractEntityInterface extends ArraySerializableInterface
{
    /**
     * Get all columns of this entity that correspond to database columns
     *
     * @return array
     */
    public function getColumns();

    /**
     * Get values which are to be saved to the database
     *
     * Useful if as_array is overridden to return values not
     * saved to the database.
     *
     * @return array
     */
    public function getDbValues();

    /**
     * Determine if this entity is new (not yet persisted) by checking for existence of an ID
     *
     * @return boolean
     */
    public function isNew();
}
