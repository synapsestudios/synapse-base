<?php

namespace Synapse\Mapper;

use Synapse\Entity\AbstractEntity;

/**
 * Use this trait to add delete functionality to AbstractMappers.
 */
trait DeleterTrait
{
    /**
     * Delete record corresponding to this entity
     *
     * @param  AbstractEntity $entity
     * @return Result
     */
    public function delete(AbstractEntity $entity)
    {
        return $this->deleteWhere([
            'id' => $entity->getId()
        ]);
    }

    /**
     * Delete all records in this table that meet the provided conditions
     *
     * @param  array  $wheres An array of where conditions in the format: ['column' => 'value']
     * @return Result
     */
    public function deleteWhere(array $wheres)
    {
        $query = $this->sql()
            ->delete()
            ->where($wheres);

        return $this->execute($query);
    }
}
