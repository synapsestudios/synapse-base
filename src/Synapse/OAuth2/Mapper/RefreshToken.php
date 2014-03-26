<?php

namespace Synapse\OAuth2\Mapper;

use Synapse\Mapper;
use Synapse\OAuth2\Entity\RefreshToken as RefreshTokenEntity;

class RefreshToken extends Mapper\AbstractMapper
{
    use Mapper\FinderTrait;

    /**
     * {@inheritDoc}
     */
    protected $tableName = 'oauth_refresh_tokens';

    /**
     * Update the given entity in the database
     *
     * @param  RefreshTokenEntity $entity
     * @return RefreshTokenEntity
     */
    public function update(RefreshTokenEntity $entity)
    {
        $dbValueArray = $entity->getDbValues();

        $condition = ['refresh_token' => $entity->getRefreshToken()];

        $query = $this->sql()
            ->update()
            ->set($dbValueArray)
            ->where($condition);

        $this->execute($query);

        return $entity;
    }
}
