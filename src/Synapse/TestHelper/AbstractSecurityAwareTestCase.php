<?php

namespace Synapse\TestHelper;

use PHPUnit_Framework_TestCase;
use Synapse\User\UserEntity;

abstract class AbstractSecurityAwareTestCase extends PHPUnit_Framework_TestCase
{

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
