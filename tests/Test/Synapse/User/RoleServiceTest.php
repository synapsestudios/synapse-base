<?php

namespace Test\Synapse\User;

use Synapse\TestHelper\TestCase;
use Synapse\User\RoleService;
use Synapse\User\UserEntity;
use stdClass;

class RoleServiceTest extends TestCase
{
    const ROLE    = 'ROLE_FOOD_BARN_BASS_QUACKS';
    const USER_ID = 3;

    public function setUp()
    {
        $this->captured = new stdClass;
        $this->setMocks(['mapper' => 'Synapse\User\UserRolePivotMapper']);
        $this->setUpMocks();

        $this->service = new RoleService($this->mocks['mapper']);

        $this->user = $this->getUser();
    }

    public function setUpMocks()
    {
        $this->mocks['mapper']->expects($this->any())
            ->method('addRoleForUser')
            ->will($this->returnCallback(function ($userId, $role) {
                $this->captured->persistedRole = [
                    'userId' => $userId,
                    'role'   => $role,
                ];
            }));
    }

    public function getUser()
    {
        return new UserEntity(['id' => self::USER_ID]);
    }

    public function testAddRoleForUserPersistsRoleToDatabase()
    {
        $this->service->addRoleForUser($this->user, self::ROLE);

        $this->assertEquals(self::ROLE, $this->captured->persistedRole['role']);
        $this->assertSame($this->user->getId(), $this->captured->persistedRole['userId']);
    }

    public function testAddRoleForUserSetsRoleOnUserEntity()
    {
        $this->assertNotContains(self::ROLE, $this->user->getRoles());

        $this->service->addRoleForUser($this->user, self::ROLE);

        $this->assertContains(self::ROLE, $this->user->getRoles());
    }
}
