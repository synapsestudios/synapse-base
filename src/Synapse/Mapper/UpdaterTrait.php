<?php

namespace Synapse\Mapper;

use Synapse\Entity\AbstractEntity;

/**
 * Use this trait to add update functionality to AbstractMappers.
 */
trait UpdaterTrait
{
    /**
     * Update the given entity in the database
     *
     * @param  AbstractEntity $entity
     * @return AbstractEntity
     */
    public function update(AbstractEntity $entity)
    {
        $values = $entity->getDbValues();

        return $this->updateRow($entity, $values);
    }

    /**
     * Update an entity's DB row by its ID.
     * Set the updated timestamp column if it exists.
     *
     * @param  AbstractEntity $entity
     * @param  array          $values Values to set on the entity
     * @return AbstractEntity
     */
    protected function updateRow(AbstractEntity $entity, array $values)
    {
        if ($this->updatedTimestampColumn) {
            $timestamp = time();

            $entity->exchangeArray([$this->updatedTimestampColumn => $timestamp]);

            $values[$this->updatedTimestampColumn] = $timestamp;
        }

        unset($values['id']);

        $condition = ['id' => $entity->getId()];

        $query = $this->getSqlObject()
            ->update()
            ->set($values)
            ->where($condition);

        $this->execute($query);

        return $entity;
    }
}
