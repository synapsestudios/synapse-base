<?php

namespace Test\Synapse\SocialLogin;

use Synapse\User\UserEntity;
use Synapse\SocialLogin\SocialLoginService;
use Synapse\SocialLogin\SocialLoginEntity;
use Synapse\SocialLogin\LoginRequest;
use PHPUnit_Framework_TestCase;
use stdClass;

class SocialLoginServiceTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->socialLoginService = new SocialLoginService();

        $this->setupMockUserService();
        $this->setupMockSocialLoginMapper();
        $this->setupMockOAuth2ZendDb();

        $this->socialLoginService->setSocialLoginMapper($this->mockSocialLoginMapper);
        $this->socialLoginService->setUserService($this->mockUserService);
        $this->socialLoginService->setOAuthStorage($this->mockOAuth2ZendDb);

        $this->captured = new stdClass();
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

    public function setupMockOAuth2ZendDb()
    {
        $this->mockOAuth2ZendDb = $this->getMockBuilder('Synapse\OAuth2\Storage\ZendDb')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function withSocialLoginMapperReturning($entity)
    {
        $this->mockSocialLoginMapper->expects($this->any())
            ->method('findByProviderUserId')
            ->will($this->returnCallback(function () use ($entity) {
                return $entity;
            }));
    }

    public function withUserFound()
    {
        $userEntity = $this->createUserEntity();

        $this->mockUserService->expects($this->any())
            ->method('findById')
            ->will($this->returnValue($userEntity));
    }

    public function capturingPersistedSocialLoginEntity()
    {
        $this->mockSocialLoginMapper->expects($this->any())
            ->method('persist')
            ->will($this->returnCallback(function ($entity) {
                $this->captured->persistedSocialLoginEntity = $entity;
                return $entity;
            }));
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
        $socialLoginEntity = $this->getSocialLoginEntity();
        $this->withSocialLoginMapperReturning($socialLoginEntity);
        $this->capturingPersistedSocialLoginEntity();
        $this->withUserFound();

        $loginRequest = $this->createLoginRequest();

        $this->socialLoginService->handleLoginRequest($loginRequest);
        $this->assertSame($socialLoginEntity, $this->captured->persistedSocialLoginEntity);
    }
}
