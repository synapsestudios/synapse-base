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
        if ($this->createdTimestampColumn) {
            $entity->exchangeArray([$this->createdTimestampColumn => time()]);
        }

        $values = $entity->getDbValues();

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
