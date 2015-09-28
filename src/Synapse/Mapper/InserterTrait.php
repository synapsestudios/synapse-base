<?php

namespace Synapse\Mapper;

use Synapse\Entity\AbstractEntity;
use LogicException;

/**
 * Use this trait to add create functionality to AbstractMappers.
 */
trait InserterTrait
{
    /**
     * Insert the given entity into the database.
     * If the entity does not have its `$this->autoIncrementColumn` field already
     * set, populate that field with the value returned from the query.
     *
     * Set the created timestamp and created datetime columns if they exist.
     *
     * @param  AbstractEntity $entity
     * @return AbstractEntity Entity with ID populated
     * @throws LogicException if autoIncrementColumn not in entity
     */
    public function insert(AbstractEntity $entity)
    {
        if ($this->createdTimestampColumn) {
            $entity->exchangeArray([$this->createdTimestampColumn => time()]);
        }

        if ($this->createdDatetimeColumn) {
            $entity->exchangeArray([$this->createdDatetimeColumn => date('Y-m-d H:i:s')]);
        }

        $this->insertRow($entity);

        return $entity;
    }

    /**
     * Insert an entity's DB row using the given values.
     * Set the ID on the entity from the query result.
     *
     * @param  AbstractEntity $entity
     */
    protected function insertRow(AbstractEntity $entity)
    {
        $values  = $entity->getDbValues();
        $columns = array_keys($values);

        if ($this->autoIncrementColumn && ! array_key_exists($this->autoIncrementColumn, $values)) {
            throw new LogicException('auto_increment column ' . $this->autoIncrementColumn . ' not found');
        }

        $query = $this->getSqlObject()
            ->insert()
            ->columns($columns)
            ->values($values);

        $statement = $this->getSqlObject()->prepareStatementForSqlObject($query);
        $result    = $statement->execute();

        if ($this->autoIncrementColumn && ! $values[$this->autoIncrementColumn]) {
            $entity->exchangeArray([
                $this->autoIncrementColumn => $result->getGeneratedValue()
            ]);
        }
    }
}
