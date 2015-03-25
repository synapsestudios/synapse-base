<?php

namespace Test\Synapse\OAuth2;

use Synapse\TestHelper\MapperTestCase;
use Synapse\OAuth2\RefreshTokenMapper;
use Synapse\OAuth2\RefreshTokenEntity;

class RefreshTokenMapperTest extends MapperTestCase
{
    const REFRESH_TOKEN = 'as90dna0s934mrze';

    public function setUp()
    {
        parent::setUp();

        $this->mapper = new RefreshTokenMapper($this->mocks['adapter'], new RefreshTokenEntity);
        $this->mapper->setSqlFactory($this->mocks['sqlFactory']);
    }

    public function createEntity()
    {
        return new RefreshTokenEntity([
            'refresh_token' => self::REFRESH_TOKEN,
            'client_id'     => 'a73n4c9a8ces',
            'user_id'       => 3917,
            'expires'       => time(),
            'scope'         => 'name',
        ]);
    }

    public function testUpdateUpdatesWhereRefreshTokenColumnMatchedRatherThanId()
    {
        $entity = $this->createEntity();

        $this->mapper->update($entity);

        $regexp = sprintf(
            '/WHERE `refresh_token` = \'%s\'/',
            self::REFRESH_TOKEN
        );

        $this->assertRegExp($regexp, $this->getSqlString());
    }
}
