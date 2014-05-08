<?php

namespace Synapse\Mapper;

use Synapse\Entity\AbstractEntity;

/**
 * Use this trait to add update functionality to AbstractMappers.
 */
trait UpdaterTrait
{
    /**
     * The name of the column where a time updated timestamp is stored
     *
     * @var string
     */
    protected $updatedTimestampColumn = null;

    /**
     * Update the given entity in the database
     *
     * @param  AbstractEntity $entity
     * @return AbstractEntity
     */
    public function update(AbstractEntity $entity)
    {
        if ($this->updatedTimestampColumn) {
            $entity->exchangeArray([$this->updatedTimestampColumn => time()]);
        }

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
