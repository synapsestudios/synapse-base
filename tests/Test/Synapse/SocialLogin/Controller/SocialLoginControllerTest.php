<?php

namespace Test\Synapse\SocialLogin\Controller;

use OutOfBoundsException;
use PHPUnit_Framework_TestCase;
use Synapse\SocialLogin\Controller\SocialLoginController;
use Synapse\SocialLogin\Exception\NoLinkedAccountException;
use Synapse\SocialLogin\Exception\LinkedAccountExistsException;
use Synapse\SocialLogin\SocialLoginService;
use TestHelper\ControllerTestCase;

class SocialLoginControllerTest extends ControllerTestCase
{
    public function setUp()
    {
        $this->controller = new SocialLoginController();

        $this->setUpMockUrlGenerator();
        $this->setUpMockSocialLoginService();
        $this->setUpMockSession();
        $this->setUpMockServiceFactory();

        $this->controller->setUrlGenerator($this->mockUrlGenerator);
        $this->controller->setSocialLoginService($this->mockSocialLoginService);
        $this->controller->setServiceFactory($this->mockServiceFactory);
        $this->controller->setSession($this->mockSession);
        $this->controller->setConfig($this->getControllerConfig());
    }

    public function getControllerConfig()
    {
        $config = [
            'redirect-url' => 'redirect-url',
            'google' => [
                'callback_route' => 'callback-route',
                'key'            => '',
                'secret'         => '',
                'scope'          => []
            ]
        ];

        $config['facebook'] = $config['google'];
        $config['github'] = $config['google'];

        return $config;
    }

    public function getExpectedRedirectUrl()
    {
        $config = $this->getControllerConfig();
        $token = $this->getReturnedAccessToken();

        return $config['redirect-url'] . '?' . http_build_query($token);
    }

    public function setUpMockSession()
    {
        $this->mockSession = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Session')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function setUpMockServiceFactory($serviceName = 'Google')
    {
        $this->mockOAuthService = $this->getMockBuilder('OAuth\OAuth2\Service\\' . $serviceName)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockProviderToken = $this->getMock('OAuth\Common\Token\TokenInterface');

        $this->mockOAuthService->expects($this->any())
            ->method('requestAccessToken')
            ->will($this->returnValue($this->mockProviderToken));

        $this->mockServiceFactory = $this->getMock('OAuth\ServiceFactory');

        $this->mockServiceFactory->expects($this->any())
            ->method('createService')
            ->with(
                $this->equalTo($serviceName),
                $this->anything(),
                $this->anything(),
                $this->anything()
            )->will($this->returnValue($this->mockOAuthService));
    }

    public function setUpMockSocialLoginService()
    {
        $this->mockSocialLoginService = $this->getMock('Synapse\SocialLogin\SocialLoginService');
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

    public function getReturnedAccessToken()
    {
        return [
            'access_token' => '12345'
        ];
    }

    public function expectingLoginRequest()
    {
        $this->mockSocialLoginService->expects($this->once())
            ->method('handleLoginRequest')
            ->with($this->anything())
            ->will($this->returnValue($this->getReturnedAccessToken()));
    }

    public function expectingLinkRequest()
    {
        $this->mockSocialLoginService->expects($this->once())
            ->method('handleLinkRequest')
            ->with($this->anything())
            ->will($this->returnValue($this->getReturnedAccessToken()));
    }

    public function expectingLoginRequestAndThrowingException($exception)
    {
        $this->mockSocialLoginService->expects($this->once())
            ->method('handleLoginRequest')
            ->with($this->anything())
            ->will($this->throwException($exception));
    }

    public function expectingGithubAsProvider()
    {
        $this->setUpMockServiceFactory('GitHub');

        $this->mockOAuthService->expects($this->at(1))
            ->method('request')
            ->with($this->equalTo('user/emails'))
            ->will($this->returnValue(json_encode(['user@domain.com'])));

        $this->mockOAuthService->expects($this->at(2))
            ->method('request')
            ->with($this->equalTo('user'))
            ->will($this->returnValue(json_encode(['id' => '123'])));

        $this->controller->setServiceFactory($this->mockServiceFactory);
    }

    public function expectingFacebookAsProvider()
    {
        $this->setUpMockServiceFactory('Facebook');

        $this->mockOAuthService->expects($this->once())
            ->method('request')
            ->with($this->equalTo('/me'))
            ->will(
                $this->returnValue(json_encode([
                    'id' => '123',
                    'email' => 'user@domain.com'
                ]))
            );

        $this->controller->setServiceFactory($this->mockServiceFactory);
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

    public function testCallbackReturns301WithRedirectLocationFromConfigWithParamsFromToken()
    {
        $this->expectingLoginRequest();

        $request = $this->createJsonRequest('get', [
            'attributes' => ['provider' => 'google'],
            'getParams'  => ['state' => SocialLoginController::ACTION_LOGIN_WITH_ACCOUNT]
        ]);

        $response = $this->controller->callback($request);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals(
            $this->getExpectedRedirectUrl(),
            $response->headers->get('Location')
        );
    }

    public function testRedirectUrlContainsErrorParamsIfNoLinkedAccountExceptionThrown()
    {
        $this->expectingLoginRequestAndThrowingException(new NoLinkedAccountException);

        $request = $this->createJsonRequest('get', [
            'attributes' => ['provider' => 'google'],
            'getParams'  => ['state' => SocialLoginController::ACTION_LOGIN_WITH_ACCOUNT]
        ]);

        $response = $this->controller->callback($request);

        $this->assertContains('login_failure=1', $response->headers->get('Location'));
        $this->assertContains('error=no_linked_account', $response->headers->get('Location'));
    }

    public function testRedirectUrlContainsErrorParamsIfLinkedAccountExistsExceptionThrown()
    {
        $this->expectingLoginRequestAndThrowingException(new LinkedAccountExistsException);

        $request = $this->createJsonRequest('get', [
            'attributes' => ['provider' => 'google'],
            'getParams'  => ['state' => SocialLoginController::ACTION_LOGIN_WITH_ACCOUNT]
        ]);

        $response = $this->controller->callback($request);

        $this->assertContains('login_failure=1', $response->headers->get('Location'));
        $this->assertContains('error=account_already_linked', $response->headers->get('Location'));
    }

    public function testRedirectUrlContainsErrorParamsOutOfBoundsExceptionThrown()
    {
        $this->expectingLoginRequestAndThrowingException(
            new OutOfBoundsException(
                '',
                SocialLoginService::EXCEPTION_ACCOUNT_NOT_FOUND
            )
        );

        $request = $this->createJsonRequest('get', [
            'attributes' => ['provider' => 'google'],
            'getParams'  => ['state' => SocialLoginController::ACTION_LOGIN_WITH_ACCOUNT]
        ]);

        $response = $this->controller->callback($request);

        $this->assertContains('login_failure=1', $response->headers->get('Location'));
        $this->assertContains('error=account_not_found', $response->headers->get('Location'));
    }

    public function testGithubCallbackImplemented()
    {
        $this->expectingGithubAsProvider();
        $this->expectingLoginRequest();

        $request = $this->createJsonRequest('get', [
            'attributes' => ['provider' => 'github'],
            'getParams'  => ['state' => SocialLoginController::ACTION_LOGIN_WITH_ACCOUNT]
        ]);

        $response = $this->controller->callback($request);
    }

    public function testFacebookCallbackImplemented()
    {
        $this->expectingFacebookAsProvider();
        $this->expectingLoginRequest();

        $request = $this->createJsonRequest('get', [
            'attributes' => ['provider' => 'facebook'],
            'getParams'  => ['state' => SocialLoginController::ACTION_LOGIN_WITH_ACCOUNT]
        ]);

        $response = $this->controller->callback($request);
    }
}
