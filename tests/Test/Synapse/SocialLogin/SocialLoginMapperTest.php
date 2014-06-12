<?php

namespace Test\Synapse\SocialLogin;

use Synapse\TestHelper\MapperTestCase;
use Synapse\SocialLogin\SocialLoginMapper;
use Synapse\SocialLogin\SocialLoginEntity;

class SocialLoginMapperTest extends MapperTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mapper = new SocialLoginMapper($this->mockAdapter, new SocialLoginEntity);
        $this->mapper->setSqlFactory($this->mockSqlFactory);
    }

    public function testFindByUserIdSearchesByUserIdOnly()
    {
        $userId = 10294857;

        $this->mapper->findByUserId($userId);

        $regexp = sprintf('/WHERE `user_id` = \'%s\'$/', $userId);

        $this->assertRegExp($regexp, $this->getSqlString());
    }

    public function testFindByProviderUserIdSearchesByProviderAndProviderUserId()
    {
        $providerUserId = 1022957;
        $provider       = 'github';

        $this->mapper->findByProviderUserId($provider, $providerUserId);

        $regexp = sprintf(
            '/WHERE `provider` = \'%s\' AND `provider_user_id` = \'%s\'$/',
            $provider,
            $providerUserId
        );

        $this->assertRegExp($regexp, $this->getSqlString());
    }
}
