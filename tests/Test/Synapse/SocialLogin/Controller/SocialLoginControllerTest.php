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
        $this->setUpMockSocialLoginService();

        $this->controller->setUrlGenerator($this->mockUrlGenerator);
        $this->controller->setSocialLoginService($this->mockSocialLoginService);
        $this->controller->setConfig([
            'google' => [
                'callback_route' => 'callback-route',
                'key'    => '',
                'secret' => '',
                'scope'  => []
            ]
        ]);
    }

    public function setUpMockSocialLoginService()
    {
        $this->mockSocialLoginService = $this->getMock('Synapse\SocialLogin\SocialLoginService');

        $this->mockOAuthService = $this->getMockBuilder('OAuth\OAuth2\Service\Google')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockProviderToken = $this->getMock('OAuth\Common\Token\TokenInterface');

        $this->mockOAuthService->expects($this->any())
            ->method('requestAccessToken')
            ->will($this->returnValue($this->mockProviderToken));

        $this->mockSocialLoginService->expects($this->any())
            ->method('getServiceByProvider')
            ->with($this->equalTo('google'))
            ->will($this->returnValue($this->mockOAuthService));
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

    public function testLinkReturns404IfProviderDoesNotExist()
    {
        $request = $this->createJsonRequest('get', [
            'attributes' => ['provider' => 'not-a-real-provider']
        ]);

        $response = $this->controller->link($request);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testLinkReturns301IfProviderExists()
    {
        $request = $this->createJsonRequest('get', [
            'attributes' => ['provider' => 'google']
        ]);

        $response = $this->controller->link($request);

        $this->assertEquals(301, $response->getStatusCode());
    }

    public function testCallbackReturns404IfProviderDoesNotExist()
    {
        $request = $this->createJsonRequest('get', [
            'attributes' => ['provider' => 'not-a-real-provider']
        ]);

        $response = $this->controller->callback($request);

        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @expectedException LogicException
     */
    public function testCallbackThrowsLoginExceptionIfProviderMethodNotImplemented()
    {
        // This test will fail if Yammer support is ever implemented.
        $request = $this->createJsonRequest('get', [
            'attributes' => ['provider' => 'yammer']
        ]);

        $response = $this->controller->callback($request);
    }

    public function testCallbackReturns422IfStateParamNotSet()
    {
        $request = $this->createJsonRequest('get', [
            'attributes' => ['provider' => 'google']
        ]);

        $response = $this->controller->callback($request);

        $this->assertEquals(422, $response->getStatusCode());
    }
}
