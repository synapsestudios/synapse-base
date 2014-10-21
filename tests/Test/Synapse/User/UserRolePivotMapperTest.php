<?php

namespace Test\Synapse\User;

use Synapse\TestHelper\MapperTestCase;
use Synapse\User\UserRolePivotMapper;
use Synapse\User\UserEntity;

class UserRolePivotMapperTest extends MapperTestCase
{
    const USER_ID = 24398;
    const ROLE_NAME = 'ROLE_ADMIN';

    public function setUp()
    {
        parent::setUp();

        $this->mapper = new UserRolePivotMapper($this->mockAdapter, new UserEntity);
        $this->mapper->setSqlFactory($this->mockSqlFactory);
    }

    public function testFindRoleNamesByUserIdSearchesByUserId()
    {
        $userId = self::USER_ID;

        $this->mapper->findRoleNamesByUserId($userId);

        $regexp = sprintf('/WHERE `user_id` = \'%s\'$/', $userId);

        $this->assertRegExpOnSqlString($regexp);
    }

    public function testFindRoleNamesByUserIdSelectsPivotColumnsAndRolesAsName()
    {
        $userId    = self::USER_ID;
        $tableName = $this->mapper->getTableName();

        $this->mapper->findRoleNamesByUserId($userId);

        $regexp = sprintf('/SELECT `%s`.*, `user_roles`.`name` AS `name` FROM `%s`/', $tableName, $tableName);

        $this->assertRegExpOnSqlString($regexp);
    }

    public function testFindRoleNamesByUserIdJoinsOnUserRolesTable()
    {
        $userId = self::USER_ID;

        $this->mapper->findRoleNamesByUserId($userId);

        $regexp = sprintf('/JOIN `user_roles` ON `user_roles`.`id` = `pvt_roles_users`.`role_id`/');

        $this->assertRegExpOnSqlString($regexp);
    }

    public function testAddRoleForUserFindsRoleId()
    {
        $userId   = self::USER_ID;
        $roleName = self::ROLE_NAME;

        $this->mapper->addRoleForUser($userId, $roleName);

        $regexp = sprintf('/\(SELECT `id` from `user_roles` WHERE `name` = \'%s\'\)/', $roleName);

        $this->assertRegExpOnSqlString($regexp);
    }

    public function testAddRoleForUserAssignsRoleToGivenUser()
    {
        $userId   = self::USER_ID;
        $roleName = self::ROLE_NAME;

        $this->mapper->addRoleForUser($userId, $roleName);

        $regexp = sprintf('/VALUES \(\'%s\'/', $userId);

        $this->assertRegExpOnSqlString($regexp);
    }
}
