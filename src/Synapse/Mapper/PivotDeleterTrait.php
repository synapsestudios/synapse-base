<?php

namespace Synapse\Mapper;

use Synapse\Entity\AbstractEntity;

/**
 * Specialized version of DeleterTrait that does not delete on the ID of an entity,
 * but rather on all columns, since pivot tables do not have an ID column.
 *
 * Use this trait to add delete functionality to AbstractMappers for pivot tables.
 */
trait PivotDeleterTrait
{
    /**
     * Delete record corresponding to this entity
     *
     * @param  AbstractEntity $entity
     * @return Result
     */
    public function delete(AbstractEntity $entity)
    {
        $conditions = $entity->getArrayCopy();

        $query = $this->sql()
            ->delete()
            ->where($conditions);

        return $this->execute($query);
    }
}
