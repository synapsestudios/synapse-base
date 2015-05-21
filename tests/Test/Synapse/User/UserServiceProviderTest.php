<?php

namespace Test\Synapse\User;

use Synapse\TestHelper\WebTestCase;
use Synapse\User\UserServiceProvider;

class UserServiceProviderTest extends WebTestCase
{
    public function setUp()
    {
        $this->setMocks([
            'userController'               => 'Synapse\User\UserController',
            'userConverter'                => 'Synapse\User\UserConverter',
            'resetPasswordController'      => 'Synapse\User\ResetPasswordController',
            'verifyRegistrationController' => 'Synapse\User\verifyRegistrationController',
        ]);

        $this->app = $this->createApplicationWithServices([new UserServiceProvider()]);

        $this->app['user.controller']                = $this->mocks['userController'];
        $this->app['user.converter']                 = $this->mocks['userConverter'];
        $this->app['reset-password.controller']      = $this->mocks['resetPasswordController'];
        $this->app['verify-registration.controller'] = $this->mocks['verifyRegistrationController'];

        $this->client = $this->createClient();
    }

    public function testCreateUserEndpointIsAccessibleAnonymously()
    {
        $this->withValidResponseFromControllerMethod('userController', 'execute');

        $this->client->post('/users');
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testVerifyRegistrationEndpointIsAccessibleAnonymously()
    {
        $this->withValidResponseFromControllerMethod('verifyRegistrationController', 'execute');

        $this->client->post('/users/1/verify-registration');
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testResetPasswordEndpointIsAccessibleAnonymously()
    {
        $this->withValidResponseFromControllerMethod('resetPasswordController', 'execute');

        $this->client->post('/user/reset-password');
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetUserIsAccessibleByAdmin()
    {
        $this->withValidResponseFromControllerMethod('userController', 'execute');
        $this->client->setBearerHeader();
        $this->withAuthenticatedRoles(['ROLE_ADMIN']);

        $this->client->get('/users/1');
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetUserIsInaccessibleWithoutAdminRole()
    {
        $this->withValidResponseFromControllerMethod('userController', 'execute');
        $this->client->setBearerHeader();

        $this->client->get('/users/1');
        $response = $this->client->getResponse();

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testGetSelfAccessibleWhileAuthenticated()
    {
        $this->withValidResponseFromControllerMethod('userController', 'execute');
        $this->client->setBearerHeader();
        $this->withAuthenticatedRoles([]);

        $this->client->get('/user');
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetSelfNotAccessibleAnonymously()
    {
        $this->withValidResponseFromControllerMethod('userController', 'execute');

        $this->client->get('/user');
        $response = $this->client->getResponse();

        $this->assertNotEquals(200, $response->getStatusCode());
    }
}
