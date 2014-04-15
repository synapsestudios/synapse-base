<?php

namespace Synapse\TestHelper;

use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Synapse\Stdlib\Arr;
use Synapse\User\UserEntity;
use stdClass;

abstract class ControllerTestCase extends PHPUnit_Framework_TestCase
{
    const LOGGED_IN_USER_ID = 79276419;

    public function createJsonRequest($method, array $params = [])
    {
        $this->request = new Request(
            Arr::get($params, 'getParams', []),
            [],
            Arr::get($params, 'attributes', []),
            [],
            [],
            [],
            Arr::get($params, 'content') ? json_encode($params['content']) : ''
        );
        $this->request->setMethod($method);
        $this->request->headers->set('CONTENT_TYPE', 'application/json');

        return $this->request;
    }

    public function setUpMockSecurityContext()
    {
        if (! isset($this->captured)) {
            $this->captured = new stdClass();
        }

        $captured = $this->captured;

        $this->mockSecurityContext = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSecurityToken = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $loggedInUserEntity = $this->getLoggedInUserEntity();

        $mockSecurityToken->expects($this->any())
            ->method('getUser')
            ->will($this->returnCallback(function () use ($loggedInUserEntity, $captured) {
                $captured->userReturnedFromSecurityContext = $loggedInUserEntity;

                return $loggedInUserEntity;
            }));

        $this->mockSecurityContext->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($mockSecurityToken));

        $this->captured = $captured;
    }

    /**
     * Return a User entity for use with the mock security object
     *
     * @return UserEntity
     */
    public function getLoggedInUserEntity()
    {
        $user = new UserEntity;

        $user->exchangeArray([
            'id'         => self::LOGGED_IN_USER_ID,
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
