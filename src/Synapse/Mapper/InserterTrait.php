<?php

namespace Synapse\Mapper;

use Synapse\Entity\AbstractEntity;

/**
 * Use this trait to add create functionality to AbstractMappers.
 */
trait InserterTrait
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

        return $this->insertRow($entity, $values);
    }

    /**
     * Insert an entity's DB row using the given values.
     * Set the ID on the entity from the query result.
     * Set the created timestamp column if it exists.
     *
     * @param  AbstractEntity $entity
     * @param  array          $values Values with which to create the entity
     * @return AbstractEntity
     */
    protected function insertRow(AbstractEntity $entity, array $values)
    {
        if ($this->createdTimestampColumn) {
            $timestamp = time();

            $entity->exchangeArray([$this->createdTimestampColumn => $timestamp]);

            $values[$this->createdTimestampColumn] = $timestamp;
        }

        $columns = array_keys($values);

        $query = $this->getSqlObject()
            ->insert()
            ->columns($columns)
            ->values($values);

        $statement = $this->getSqlObject()->prepareStatementForSqlObject($query);

        $result = $statement->execute();

        $entity->setId($result->getGeneratedValue());

        return $entity;
    }
}
