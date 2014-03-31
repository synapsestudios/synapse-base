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
        $this->setUpMockSession();

        $this->controller->setUrlGenerator($this->mockUrlGenerator);
        $this->controller->setSocialLoginService($this->mockSocialLoginService);
        $this->controller->setSession($this->mockSession);
        $this->controller->setConfig([
            'redirect-url' => 'redirect-url',
            'google' => [
                'callback_route' => 'callback-route',
                'key'            => '',
                'secret'         => '',
                'scope'          => []
            ]
        ]);
    }

    public function setUpMockSession()
    {
        $this->mockSession = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Session')
            ->disableOriginalConstructor()
            ->getMock();
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

    public function expectingLoginRequest()
    {
        $this->mockSocialLoginService->expects($this->once())
            ->method('handleLoginRequest')
            ->with($this->anything())
            ->will($this->returnValue(['access_token' => '12345']));
    }

    public function expectingLinkRequest()
    {
        $this->mockSocialLoginService->expects($this->once())
            ->method('handleLinkRequest')
            ->with($this->anything())
            ->will($this->returnValue(['access_token' => '12345']));
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

    public function testHandleLoginRequestCalledIfStateIsLogin()
    {
        $this->expectingLoginRequest();

        $request = $this->createJsonRequest('get', [
            'attributes' => ['provider' => 'google'],
            'getParams'  => ['state' => SocialLoginController::ACTION_LOGIN_WITH_ACCOUNT]
        ]);

        $response = $this->controller->callback($request);
    }

    public function testHandleLoginRequestCalledIfStateIsLink()
    {
        $this->expectingLinkRequest();

        $request = $this->createJsonRequest('get', [
            'attributes' => ['provider' => 'google'],
            'getParams'  => ['state' => SocialLoginController::ACTION_LINK_ACCOUNT]
        ]);

        $response = $this->controller->callback($request);
    }
}
