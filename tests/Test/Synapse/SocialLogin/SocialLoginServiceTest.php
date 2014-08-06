<?php

namespace Test\Synapse\SocialLogin;

use Synapse\SocialLogin\SocialLoginService;
use Synapse\SocialLogin\SocialLoginEntity;
use PHPUnit_Framework_TestCase;

class SocialLoginServiceTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->socialLoginService = new SocialLoginService();

        $this->createMocks();

        $this->socialLoginService->setSocialLoginMapper($this->mockSocialLoginMapper);
    }

    public function createMocks()
    {
        $this->mockSocialLoginMapper = $this->getMockBuilder('Synapse\SocialLogin\SocialLoginMapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockSocialLoginMapper->expects($this->any())
            ->method('persist')
            ->will($this->returnCallback(function ($entity) {
                return $entity;
            }));
    }

    public function getSocialLoginEntity()
    {
        $socialLoginEntity = new SocialLoginEntity();

        $socialLoginEntity->exchangeArray([
            'provider' => 'facebook',
            'access_token' => 'CAADm7v02lKgBAEvIkmGlpWpvzWVrPo2mnuJHjj4',
            'access_token_expires' => '1412531896'
        ]);

        return $socialLoginEntity;
    }

    public function testTokenUpdateOnLogin()
    {
        $socialLoginEntity = $this->getSocialLoginEntity();


        $this->assertTrue(false);
    }
}
