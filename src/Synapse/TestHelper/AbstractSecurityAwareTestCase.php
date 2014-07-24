<?php

namespace Synapse\TestHelper;

use PHPUnit_Framework_TestCase;
use Synapse\User\UserEntity;

/**
 * Extend this class to create mocks of the security token and context for testing
 */
abstract class AbstractSecurityAwareTestCase extends PHPUnit_Framework_TestCase
{
    const USER_ID = 42;

    /**
     * Set up the mock security context
     *
     * `getToken` returns a mocked security token whose getUser method returns a UserEntity.
     * Customize the user returned by overloading getDefaultLoggedInUserEntity.
     */
    public function setUpMockSecurityContext()
    {
        $this->mockSecurityContext = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSecurityToken = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $mockSecurityToken->expects($this->any())
            ->method('getUser')
            ->will($this->returnCallback(function () {
                return $this->getDefaultLoggedInUserEntity();
            }));

        $this->mockSecurityContext->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($mockSecurityToken));
    }

    /**
     * Return a Mocked UserEntity object
     * @return UserEntity
     */
    public function getDefaultLoggedInUserEntity()
    {
        $user = new UserEntity;

        $user->exchangeArray([
            'id'         => self::USER_ID,
            'email'      => 'test@example.com',
            'password'   => 'password',
            'last_login' => 1397078025,
            'created'    => 1397077825,
            'enabled'    => 1,
            'verified'   => 1,
        ]);

        return $user;
    }
}
