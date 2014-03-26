<?php

namespace SynapseTest\Entity;

use PHPUnit_Framework_TestCase;
use Synapse\User\Entity\User;

class UserTest extends PHPUnit_Framework_TestCase
{
    public function testSetGetRoles()
    {
        $roles = [
            'ROLE_ADMIN',
            'ROLE_USER'
        ];

        $user = new User;
        $user->setRoles($roles);

        $this->assertEquals($roles, $user->getRoles());
    }

    public function testUserGetters()
    {
        $user = new User;
        $user->fromArray([
            'email'    => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('test@example.com', $user->getUsername());
        $this->assertEquals('password', $user->getPassword());

        // We use password_hash so we don't store a separate salt
        $this->assertNull($user->getSalt());

        // Meant to be a no-op
        $this->assertNull($user->eraseCredentials());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSettingInvalidPropertyThrowsException()
    {
        $user = new User;
        $user->setSomethingThatDoesNotExist('fail');
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testMissingMethodThrowsBadMethodCallException()
    {
        $user = new User;
        $user->someMethodThatDoesNotExist();
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testMissingMethodThrowsBadMethodCallException2()
    {
        $user = new User;
        $user->abc();
    }

    public function testAbstractGetColumns()
    {
        $columns = [
            'id',
            'email',
            'password',
            'last_login',
            'created',
            'enabled',
            'verified',
        ];

        $user = new User;
        $this->assertEquals($columns, $user->getColumns());
    }

    public function testGetDbValues()
    {
        $values = [
            'id'         => null,
            'email'      => null,
            'password'   => null,
            'last_login' => null,
            'created'    => null,
            'enabled'    => null,
            'verified'   => null,
        ];

        $user = new User;

        $this->assertEquals($values, $user->getDbValues());
    }

    public function testIsNew()
    {
        $user = new User;

        $this->assertTrue($user->isNew());
        $user->setId(5);
        $this->assertFalse($user->isNew());
    }
}
