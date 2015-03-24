<?php

namespace Synapse\Mapper;

use Synapse\Entity\AbstractEntity;

/**
 * Use this trait to add update functionality to AbstractMappers.
 */
trait UpdaterTrait
{
    /**
     * Update the given entity in the database.
     *
     * @param  AbstractEntity $entity
     * @return AbstractEntity
     */
    public function update(AbstractEntity $entity)
    {
        if ($this->updatedTimestampColumn) {
            $entity->exchangeArray([$this->updatedTimestampColumn => time()]);
        }

        if ($this->updatedDatetimeColumn) {
            $entity->exchangeArray([$this->updatedDatetimeColumn => date('Y-m-d H:i:s')]);
        }

        $this->updateRow($entity);

        return $entity;
    }

    /**
     * Update an entity's DB row by its ID.
     * Set the updated timestamp column if it exists.
     *
     * @param  AbstractEntity $entity
     * @param  array          $values Values to set on the entity
     */
    protected function updateRow(AbstractEntity $entity)
    {
        $values = $entity->getDbValues();

        $query = $this->getSqlObject()
            ->update()
            ->set($values)
            ->where($this->getPrimaryKeyWheres($entity));

        $this->execute($query);
    }
}
