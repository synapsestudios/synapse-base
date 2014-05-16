<?php

namespace Synapse\OAuth2;

use Synapse\Mapper;
use Synapse\OAuth2\Entity\AccessToken as AccessTokenEntity;

class AccessTokenMapper extends Mapper\AbstractMapper
{
    use Mapper\FinderTrait;

    /**
     * {@inheritDoc}
     */
    protected $tableName = 'oauth_access_tokens';

    /**
     * Update the given entity in the database
     *
     * @param  AccessTokenEntity $entity
     * @return AccessTokenEntity
     */
    public function update(AccessTokenEntity $entity)
    {
        $dbValueArray = $entity->getDbValues();

        $condition = ['access_token' => $entity->getAccessToken()];

        $query = $this->getSqlObject()
            ->update()
            ->set($dbValueArray)
            ->where($condition);

        $this->execute($query);

        return $entity;
    }
}
