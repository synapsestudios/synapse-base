<?php

namespace Test\Synapse\User;

use Synapse\TestHelper\MapperTestCase;
use Synapse\User\UserRolePivotMapper;
use Synapse\User\UserEntity;

class UserRolePivotMapperTest extends MapperTestCase
{
    const USER_ID = 24398;

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

        $regexp = sprintf('/JOIN `user_roles` ON `user_roles`.`id` = `pvt_roles_users`.`role_id`/', $userId);

        $this->assertRegExpOnSqlString($regexp);
    }
}
