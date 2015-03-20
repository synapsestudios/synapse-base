<?php

namespace Synapse\TestHelper;

use Synapse\User\UserEntity;
use stdClass;

trait SecurityContextMockInjector
{
    protected static $loggedInUserId = 42;

    /**
     * @var mixed  UserEntity or null to simulate the user not being logged in
     */
    protected $loggedInUserEntity = false;

    /**
     * Set up the mock security context
     *
     * `getToken` returns a mocked security token whose getUser method returns a UserEntity.
     * Customize the user returned by overloading getDefaultLoggedInUserEntity.
     */
    public function setUpMockSecurityContext()
    {
        if (! isset($this->captured)) {
            $this->captured = new stdClass();
        }

        $this->mocks['securityContext'] = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSecurityToken = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $mockSecurityToken->expects($this->any())
            ->method('getUser')
            ->will($this->returnCallback(function () {
                $this->captured->userReturnedFromSecurityContext = $this->getLoggedInUserEntity();
                return $this->captured->userReturnedFromSecurityContext;
            }));

        $this->mocks['securityContext']->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($mockSecurityToken));
    }

    /**
     * Return a Mocked UserEntity object
     *
     * @return UserEntity
     */
    public function getDefaultLoggedInUserEntity()
    {
        $user = new UserEntity;

        $user->exchangeArray([
            'id'         => self::$loggedInUserId,
            'email'      => 'test@example.com',
            'password'   => 'password',
            'last_login' => 1397078025,
            'created'    => 1397077825,
            'enabled'    => 1,
            'verified'   => 1,
        ]);

        return $user;
    }

    /**
     * Return a User entity for use with the mock security object
     *
     * @return UserEntity or null
     */
    public function getLoggedInUserEntity()
    {
        // If not changed from initial value, return default.
        if ($this->loggedInUserEntity === false) {
            $this->loggedInUserEntity = $this->getDefaultLoggedInUserEntity();
        }

        return $this->loggedInUserEntity;
    }

    /**
     * @param mixed $user  UserEntity or null to simulate the user not being logged in
     */
    public function setLoggedInUserEntity($user)
    {
        $this->loggedInUserEntity = $user;
    }
}
