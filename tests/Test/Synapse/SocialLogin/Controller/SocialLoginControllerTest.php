<?php

namespace Test\Synapse\SocialLogin\Controller;

use PHPUnit_Framework_TestCase;
use Synapse\SocialLogin\Controller\SocialLoginController;
use TestHelper\ControllerTestCase;

class SocialLoginControllerTest extends ControllerTestCase
{
    public function setUp()
    {
        $this->controller = new SocialLoginController();

        $this->setUpMockUrlGenerator();

        $this->controller->setUrlGenerator($this->mockUrlGenerator);
        $this->controller->setConfig([
            'google' => [
                'callback_route' => 'callback-route',
                'key'    => '',
                'secret' => '',
                'scope'  => []
            ]
        ]);
    }

    public function setUpMockUrlGenerator()
    {
        $this->mockUrlGenerator = $this->getMock(
            'Symfony\Component\Routing\Generator\UrlGeneratorInterface'
        );
        $this->mockUrlGenerator->expects($this->any())
            ->method('generate')
            ->will($this->returnValue('/url'));
    }

    public function testLoginReturns404IfProviderDoesNotExist()
    {
        $request = $this->createJsonRequest('get', [
            'attributes' => ['provider' => 'not-a-real-provider']
        ]);

        $response = $this->controller->login($request);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testLoginReturns301IfProviderExists()
    {
        $request = $this->createJsonRequest('get', [
            'attributes' => ['provider' => 'google']
        ]);

        $response = $this->controller->login($request);

        $this->assertEquals(301, $response->getStatusCode());
    }

    public function testLoginReturnsResponseWithRedirectHeaderThatIncludesLoginConstantValueForTheStateParam()
    {
        $request = $this->createJsonRequest('get', [
            'attributes' => ['provider' => 'google']
        ]);

        $response = $this->controller->login($request);

        $this->assertContains(
            'state=' . SocialLoginController::ACTION_LOGIN_WITH_ACCOUNT,
            $response->headers->get('location')
        );
    }

    public function testLinkReturnsResponseWithRedirectHeaderThatIncludesLinkConstantValueForTheStateParam()
    {
        $request = $this->createJsonRequest('get', [
            'attributes' => ['provider' => 'google']
        ]);

        $response = $this->controller->link($request);

        $this->assertContains(
            'state=' . SocialLoginController::ACTION_LINK_ACCOUNT,
            $response->headers->get('location')
        );
    }
}
