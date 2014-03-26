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
        $condition = [
            'id' => $entity->getId()
        ];

        $query = $this->sql()
            ->delete()
            ->where($condition);

        return $this->execute($query);
    }
}
