<?php

namespace Synapse\Entity;

use Iterator;
use InvalidArgumentException;
use LogicException;
use Synapse\Mapper\PaginationData;
use Zend\Stdlib\ArraySerializableInterface;

/**
 * An iterator for AbstractEntity objects
 */
class EntityIterator implements ArraySerializableInterface, Iterator
{
    /**
     * @var array Array of AbstractEntity objects
     */
    protected $entities = array();

    /**
     * @var Synapse\Mapper\PaginationData
     */
    protected $paginationData;

    /**
     * The iterator position
     *
     * @var integer
     */
    protected $position = 0;

    /**
     * @param array $entities Array of AbstractEntity objects
     */
    public function __construct(array $entities = array())
    {
        $this->setEntities($entities);
    }

    /**
     * Set the entities wrapped by this class
     *
     * @param  array $entities Array of AbstractEntity objects
     *
     * @throws InvalidArgumentException If all elements of $entities are not AbstractEntity objects
     */
    public function setEntities(array $entities)
    {
        foreach ($entities as $entity) {
            if (! $entity instanceof AbstractEntity) {
                $message = sprintf(
                    'Expected an array of entities. Array contains element of type %s.',
                    gettype($entity)
                );

                throw new InvalidArgumentException($message);
            }
        }

        $this->entities = $entities;
    }

    /**
     * Get the array of entities that this class represents
     *
     * @return array[AbstractEntity]
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * Get the pagination data (current page, page count, result count)
     *
     * @return PaginationData
     */
    public function getPaginationData()
    {
        return $this->paginationData;
    }

    /**
     * Set the current page, page count, and result count
     *
     * @param  PaginationData $paginationData
     */
    public function setPaginationData(PaginationData $paginationData)
    {
        $this->paginationData = $paginationData;
        return $this;
    }

    /**
     * Exchange internal values from provided array
     *
     * @param  array $array
     * @throws LogicException To denote that this method is not implemented
     */
    public function exchangeArray(array $array)
    {
        throw new LogicException('EntityIterator::exchangeArray is not implemented.');
    }

    /**
     * Return an array representation of the object
     *
     * Useful for turning the array of entities into a nested array,
     * which is then ready to be JSON encoded in a REST response.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        $results = array_map(function ($entity) {
            return $entity->getArrayCopy();
        }, $this->entities);

        if (! $this->paginationData) {
            return $results;
        } else {
            $data = $this->paginationData->getArrayCopy();
            $data['results'] = $results;
            return $data;
        }
    }

    /**********************************
     * Methods inherited from Iterator
     **********************************/

    /**
     * {@inheritDoc}
     */
    public function current()
    {
        return $this->entities[$this->position];
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        $this->position += 1;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function valid()
    {
        return isset($this->entities[$this->position]);
    }
}
