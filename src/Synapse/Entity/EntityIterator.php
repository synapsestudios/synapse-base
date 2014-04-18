<?php

namespace Synapse\Entity;

use Zend\Stdlib\ArraySerializableInterface;
use LogicException;
use InvalidArgumentException;

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
     * The current page represented by this iterator
     *
     * @var int
     */
    protected $page;

    /**
     * Total pages available for a paginated result set
     *
     * @var int
     */
    protected $pageCount;

    /**
     * Total results available for a paginated result set
     *
     * @var int
     */
    protected $resultCount;

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
     * @param int $page        The current page represented by this iterator
     * @param int $pageCount   Total pages available for a paginated result set
     * @param int $resultCount Total results available for a paginated result set
     */
    public function setPaginationData($page, $pageCount, $resultCount)
    {
        $this->page = $page;
        $this->pageCount = $pageCount;
        $this->resultCount = $resultCount;
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

        if (! $this->pageCount) {
            return $results;
        } else {
            return [
                'page'         => $this->page,
                'page_count'   => $this->pageCount,
                'result_count' => $this->resultCount,
                'results'      => $results
            ];
        }
    }
}
