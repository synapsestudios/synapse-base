<?php

namespace Synapse\TestHelper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;
use Synapse\Stdlib\Arr;
use Synapse\User\UserEntity;
use stdClass;

abstract class ControllerTestCase extends AbstractSecurityAwareTestCase
{
    const LOGGED_IN_USER_ID = 79276419;

    /**
     * @var mixed  UserEntity or null to simulate the user not being logged in
     */
    protected $loggedInUserEntity = false;

    public function createJsonRequest($method, array $params = [])
    {
        $this->request = new Request(
            Arr::get($params, 'getParams', []),
            [],
            Arr::get($params, 'attributes', []),
            [],
            [],
            [],
            Arr::get($params, 'content') !== null ? json_encode($params['content']) : ''
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

        $this->mockSecurityContext = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSecurityToken = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $mockSecurityToken->expects($this->any())
            ->method('getUser')
            ->will($this->returnCallback(function () {
                $this->captured->userReturnedFromSecurityContext = $this->getLoggedInUserEntity();

                return $this->captured->userReturnedFromSecurityContext;
            }));

        $this->mockSecurityContext->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($mockSecurityToken));
    }

    public function getDefaultLoggedInUserEntity()
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

    public function createNonEmptyConstraintViolationList()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Validator\ConstraintViolation')
            ->disableOriginalConstructor();

        $violations = [
            $builder->getMock(),
            $builder->getMock(),
        ];

        return new ConstraintViolationList($violations);
    }
}
