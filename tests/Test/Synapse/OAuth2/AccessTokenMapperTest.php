<?php

namespace Test\Synapse\OAuth2;

use Synapse\TestHelper\MapperTestCase;
use Synapse\OAuth2\AccessTokenMapper;
use Synapse\OAuth2\AccessTokenEntity;

class AccessTokenMapperTest extends MapperTestCase
{
    const ACCESS_TOKEN = 'as90dna0s934mrze';

    public function setUp()
    {
        parent::setUp();

        $this->mapper = new AccessTokenMapper($this->mocks['adapter'], new AccessTokenEntity);
        $this->mapper->setSqlFactory($this->mocks['sqlFactory']);
    }

    public function createEntity()
    {
        return new AccessTokenEntity([
            'access_token' => self::ACCESS_TOKEN,
            'client_id'    => 'a73n4c9a8ces',
            'user_id'      => 3917,
            'expires'      => time(),
            'scope'        => 'name',
        ]);
    }

    public function testUpdateUpdatesWhereAccessTokenColumnMatchedRatherThanId()
    {
        $entity = $this->createEntity();

        $this->mapper->update($entity);

        $regexp = sprintf(
            '/WHERE `access_token` = \'%s\'/',
            self::ACCESS_TOKEN
        );

        $this->assertRegExp($regexp, $this->getSqlString());
    }
}
