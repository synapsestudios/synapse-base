<?php

namespace Synapse\TestHelper;

use Synapse\Security\SecurityAwareInterface;
use Synapse\User\UserEntity;
use stdClass;

trait SecurityContextMockInjector
{
    /**
     * @var mixed  UserEntity or null to simulate the user not being logged in
     */
    protected $loggedInUserEntity = false;

    public function injectMockSecurityContext(SecurityAwareInterface $injectee)
    {
        $this->setUpMockSecurityContext();

        $injectee->setSecurityContext($this->mocks['securityContext']);
    }

    /**
     * Set up the mock security context
     *
     * `getToken` returns a mocked security token whose getUser method returns a UserEntity.
     */
    protected function setUpMockSecurityContext()
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
     * Return a User entity for use with the mock security object
     *
     * @return UserEntity or null
     */
    public function getLoggedInUserEntity()
    {
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
