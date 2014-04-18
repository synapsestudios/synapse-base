<?php

namespace Synapse\Entity;

use InvalidArgumentException;
use LogicException;
use Synapse\Mapper\PaginationData;
use Zend\Stdlib\ArraySerializableInterface;

/**
 * An iterator for AbstractEntity objects
 */
class EntityIterator implements ArraySerializableInterface
{
    /**
     * @var array Array of AbstractEntity objects
     */
    protected $entities;

    /**
     * @var Synapse\Mapper\PaginationData
     */
    protected $paginationData;

    /**
     * @param array $entities Array of AbstractEntity objects
     */
    public function __construct(array $entities = null)
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
     * Set the current page, page count, and result count
     *
     * @param  PaginationData $paginationData
     */
    public function setPaginationData(PaginationData $paginationData)
    {
        $this->paginationData = $paginationData;
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
}
