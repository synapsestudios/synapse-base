<?php

namespace Synapse\Mapper;

use Synapse\Entity\AbstractEntity;

/**
 * Specialized version of InserterTrait that does not set the ID on an inserted entity,
 * since pivot tables do not have an ID column.
 *
 * Use this trait to add create functionality to AbstractMappers for pivot tables.
 */
trait PivotInserterTrait
{
    /**
     * Insert the given entity into the database
     *
     * @param  AbstractEntity $entity
     * @return AbstractEntity         Entity with ID populated
     */
    public function insert(AbstractEntity $entity)
    {
        $values = $entity->getDbValues();

        $columns = array_keys($values);

        $query = $this->getSqlObject()
            ->insert()
            ->columns($columns)
            ->values($values);

        $statement = $this->getSqlObject()->prepareStatementForSqlObject($query);

        $result = $statement->execute();

        return $entity;
    }
}
