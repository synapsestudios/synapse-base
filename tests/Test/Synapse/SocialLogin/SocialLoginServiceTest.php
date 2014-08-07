<?php

namespace Test\Synapse\SocialLogin;

use Synapse\SocialLogin\SocialLoginService;
use Synapse\SocialLogin\SocialLoginEntity;
use Synapse\SocialLogin\LoginRequest;
use PHPUnit_Framework_TestCase;

class SocialLoginServiceTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->socialLoginService = new SocialLoginService();

        $this->createMocks();
        $this->setupMockUserService();
        $this->setupMockSocialLoginMapper();

        $this->socialLoginService->setSocialLoginMapper($this->mockSocialLoginMapper);
        $this->socialLoginService->setUserService($this->mockUserService);
    }

    public function withSocialLoginMapperReturningEntity()
    {
        $this->mockSocialLoginMapper->expects($this->any())
            ->method('findByProviderUserId')
            ->will($this->returnCallback(function () {
                return $this->getSocailLoginEntity();
            }));
    }

    public function withUserFound()
    {
        $userEntity = $this->createUserEntity();

        $this->mockUserService->expects($this->any())
            ->method('findById')
            ->will($this->returnValue($userEntity));
    }

    public function setupMockUserService()
    {
        $this->mockUserService = $this->getMockBuilder('Synapse\User\UserService')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function setupMockSocialLoginMapper()
    {
        $this->mockSocialLoginMapper = $this->getMockBuilder('Synapse\SocialLogin\SocialLoginMapper')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function createUserEntity()
    {
        return new UserEntity([
            'id'         => 1,
            'email'      => 'fake@fake.com',
            'password'   => 'password',
            'last_login' => '12345',
            'created'    => '123',
            'enabled'    => true,
            'verified'   => true,
        ]);
    }

    public function getSocialLoginEntity()
    {
        $socialLoginEntity = new SocialLoginEntity();

        $socialLoginEntity->exchangeArray([
            'provider' => 'facebook',
            'access_token' => 'CAADm7v02lKgBAEvIkmGlpWpvzWVrPo2mnuJHjj4',
            'access_token_expires' => 1412531896,
            'refresh_token' => null
        ]);

        return $socialLoginEntity;
    }

    public function createLoginRequest()
    {
        $loginRequest = new LoginRequest(
            'facebook',
            1,
            'fake_auth',
            1512531896,
            'fake_refresh',
            ['aaron@syn0.com']
        );

        return $loginRequest;
    }

    public function testTokenUpdateOnLogin()
    {
        $this->withSocialLoginMapperReturningEntity();
        $this->withUserFound();

        $loginRequest = $this->createLoginRequest();


        $this->assertTrue(false);
    }
}
