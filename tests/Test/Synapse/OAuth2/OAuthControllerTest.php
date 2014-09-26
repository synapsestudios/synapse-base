<?php

namespace Test\Synapse\OAuth2;

use Synapse\User\UserEntity;
use Synapse\TestHelper\ControllerTestCase;
use Synapse\OAuth2\OAuthController;
use stdClass;
use Symfony\Component\HttpFoundation\Request;

class OAuthControllerTest extends ControllerTestCase
{
    public function setUp()
    {
        $this->captured = new stdClass();

        $this->setUpMockOAuth2Server();
        $this->setUpMockUserService();
        $this->setUpMockAccessTokenMapper();
        $this->setUpMockRefreshTokenMapper();
        $this->setUpMockMustacheEngine();
        $this->setUpMockSession();
        $this->setUpMockUrlGenerator();

        $this->controller = new OAuthController(
            $this->mockOAuth2Server,
            $this->mockUserService,
            $this->mockAccessTokenMapper,
            $this->mockRefreshTokenMapper,
            $this->mockMustacheEngine,
            $this->mockSession
        );

        $this->controller->setUrlGenerator($this->mockUrlGenerator);
    }

    public function setUpMockOAuth2Server()
    {
        $this->mockOAuth2Server = $this->getMockBuilder('OAuth2\Server')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function setUpMockUserService()
    {
        $this->mockUserService = $this->getMockBuilder('Synapse\User\UserService')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function setUpMockAccessTokenMapper()
    {
        $this->mockAccessTokenMapper = $this->getMockBuilder('Synapse\OAuth2\AccessTokenMapper')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function setUpMockRefreshTokenMapper()
    {
        $this->mockRefreshTokenMapper = $this->getMockBuilder('Synapse\OAuth2\RefreshTokenMapper')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function setUpMockMustacheEngine()
    {
        $this->mockMustacheEngine = $this->getMockBuilder('Mustache_Engine')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function setUpMockSession()
    {
        $this->mockSession = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Session')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function setUpMockUrlGenerator()
    {
        $this->mockUrlGenerator = $this->getMockBuilder('Symfony\Component\Routing\Generator\UrlGeneratorInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function expectingTemplateVarsSet($vars)
    {
        $this->mockMustacheEngine->expects($this->once())
            ->method('render')
            ->with($this->anything(), $this->contains($vars));
    }

    public function capturingMustacheRenderArguments()
    {
        $this->mockMustacheEngine->expects($this->once())
            ->method('render')
            ->will($this->returnCallback(function ($templateName, $parameters) {
                $this->captured->renderedTemplateName       = $templateName;
                $this->captured->parametersPassedToTemplate = $parameters;
                $this->captured->renderedTemplate           = new stdClass();

                return $this->captured->renderedTemplate;
            }));
    }

    public function capturingRouteNameFromWhichUrlWasGenerated()
    {
        $this->mockUrlGenerator->expects($this->once())
            ->method('generate')
            ->will($this->returnCallback(function ($routeName) {
                $this->captured->routeNameFromWhichUrlWasGenerated = $routeName;
                $this->captured->generatedUrl                      = new stdClass();

                return $this->captured->generatedUrl;
            }));
    }

    public function withUserNotFound()
    {
        $this->mockUserService->expects($this->any())
            ->method('findByEmail')
            ->will($this->returnValue(false));
    }

    public function withUserFoundHavingPassword($password, $attributes = [])
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $attributes['password'] = $hashedPassword;

        $user = new UserEntity($attributes);

        $this->mockUserService->expects($this->any())
            ->method('findByEmail')
            ->will($this->returnValue($user));
    }

    public function withHandleAuthorizeRequestReturningResponse()
    {
        $this->mockOAuth2Server->expects($this->any())
            ->method('handleAuthorizeRequest')
            ->will($this->returnCallback(function() {
                $response = new stdClass();

                $this->captured->responseReturnedByOAuthServer = $response;

                return $response;
            }));
    }

    public function performPostRequestToAuthorizeFormSubmit($postParams = [])
    {
        $request = new Request([], $postParams);

        return $this->controller->authorizeFormSubmit($request);
    }

    public function testAuthorizeReturnsRenderedOAuthAuthorizeMustacheTemplate()
    {
        $expectedTemplateName = OAuthController::AUTHORIZE_FORM_TEMPLATE;

        $this->capturingMustacheRenderArguments();

        $response = $this->controller->authorize(new Request);

        $this->assertSame(
            $this->captured->renderedTemplate,
            $response
        );

        $this->assertEquals(
            $expectedTemplateName,
            $this->captured->renderedTemplateName
        );
    }

    public function testAuthorizeSetsHttpQueryParamsAsTemplateVars()
    {
        $params = [
            'foo' => 1,
            'bar' => 'baz',
        ];

        $this->expectingTemplateVarsSet([
            [
                'name'  => 'foo',
                'value' => 1,
            ],
            [
                'name'  => 'bar',
                'value' => 'baz',
            ],
        ]);

        $request = $this->createJsonRequest('GET', [
            'getParams' => $params,
        ]);

        $this->controller->authorize($request);
    }

    public function testAuthorizeSetsSubmitUrlToGeneratedAuthorizeFormSubmitUrl()
    {
        $expectedRouteName = OAuthController::AUTHORIZE_FORM_SUBMIT_ROUTE_NAME;

        $this->capturingRouteNameFromWhichUrlWasGenerated();
        $this->capturingMustacheRenderArguments();

        $this->controller->authorize(new Request);

        $this->assertEquals(
            $expectedRouteName,
            $this->captured->routeNameFromWhichUrlWasGenerated
        );

        $this->assertSame(
            $this->captured->generatedUrl,
            $this->captured->parametersPassedToTemplate['submitUrl']
        );
    }

    public function testAuthorizeFormSubmitReturns422IfUserNotFound()
    {
        $this->withUserNotFound();

        $response = $this->performPostRequestToAuthorizeFormSubmit();

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testAuthorizeFormSubmitReturns422IfPasswordIncorrect()
    {
        $password = 'password';

        // Will return 422 if user not found regardless of password, so ensure that doesn't happen
        $this->withUserFoundHavingPassword($password);

        $response = $this->performPostRequestToAuthorizeFormSubmit();

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testAuthorizeFormSubmitReturnsResponseFromOAuthServerIfCredentialsValid()
    {
        $password = 'foo';

        $this->withUserFoundHavingPassword($password);
        $this->withHandleAuthorizeRequestReturningResponse();

        $response = $this->performPostRequestToAuthorizeFormSubmit([
            'password' => $password,
        ]);

        $this->assertSame($this->captured->responseReturnedByOAuthServer, $response);
    }

    public function testAuthorizeFormSubmitSendsExpectedParametersToHandleAuthorizeRequest()
    {
        $userId   = 123;
        $password = 'foo';

        $this->mockOAuth2Server->expects($this->once())
            ->method('handleAuthorizeRequest')
            ->with(
                $this->isInstanceOf('OAuth2\HttpFoundationBridge\Request'),
                $this->isInstanceOf('OAuth2\HttpFoundationBridge\Response'),
                $this->equalTo(true),
                $this->equalTo($userId)
            );

        $this->withUserFoundHavingPassword($password, ['id' => $userId]);

        $this->performPostRequestToAuthorizeFormSubmit([
            'password' => $password,
        ]);
    }
}
