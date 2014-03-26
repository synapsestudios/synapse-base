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
        $dbValueArray = $entity->getDbValues();

        unset($dbValueArray['id']);

        $condition = ['id' => $entity->getId()];

        $query = $this->sql()
            ->update()
            ->set($dbValueArray)
            ->where($condition);

        $this->execute($query);

        return $entity;
    }
}
