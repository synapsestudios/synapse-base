<?php

namespace Test\Synapse\User;

use Synapse\TestHelper\MapperTestCase;
use Synapse\User\UserMapper;
use Synapse\User\UserEntity;

class UserMapperTest extends MapperTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mapper = new UserMapper($this->mockAdapter, new UserEntity);
        $this->mapper->setSqlFactory($this->mockSqlFactory);
    }

    public function createUserEntity()
    {
        return new UserEntity([
            'id'         => 1234,
            'email'      => 'test@website.com',
            'password'   => 'password',
            'last_login' => time(),
            'created'    => time(),
            'enabled'    => true,
            'verified'   => false,
        ]);
    }

    public function provideClassNames()
    {
        return [
            ['UserEntity'],
            ['foo'],
            [123],
            [function () {return 'This is a closure';}],
            [new \stdClass()],
        ];
    }

    public function testFindByEmailSearchesByEmailOnly()
    {
        $email = 'testing123@example.com';

        $this->mapper->findByEmail($email);

        $regexp = sprintf('/WHERE `email` = \'%s\'$/', $email);

        $this->assertRegExp($regexp, $this->getSqlString());
    }

    public function testLoadUserByUsernameIsAliasOfFindsByEmail()
    {
        $email = 'testing123@example.com';

        $this->mapper->loadUserByUsername($email);

        $regexp = sprintf('/WHERE `email` = \'%s\'$/', $email);

        $this->assertRegExp($regexp, $this->getSqlString());
    }

    public function testRefreshUserGetsUserById()
    {
        $userEntity = $this->createUserEntity();

        $this->mapper->refreshUser($userEntity);

        $regexp = sprintf('/WHERE `id` = \'%s\'$/', $userEntity->getId());

        $this->assertRegExp($regexp, $this->getSqlString());
    }

    /**
     * supportsClass is part of UserProviderInterface, but instead of implementing it we are just returning true
     *
     * @dataProvider provideClassNames
     */
    public function testSupportsClassAlwaysReturnsTrue($className)
    {
        $this->assertTrue($this->mapper->supportsClass($className));
    }
}
