<?php

namespace SynapseTest\User\Controller;

use OutOfBoundsException;
use PHPUnit_Framework_TestCase;
use stdClass;
use Synapse\Stdlib\Arr;
use Synapse\User\Controller\UserController;
use Synapse\User\Entity\User;
use Synapse\User\UserService;
use Symfony\Component\HttpFoundation\Request;

class UserControllerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->captured = new stdClass();

        $this->userController = new UserController;

        $this->setUpMockUserService();
        $this->setUpMockUrlGenerator();

        $this->userController->setUserService($this->mockUserService);
        $this->userController->setUrlGenerator($this->mockUrlGenerator);
    }

    public function setUpExistingUser()
    {
        $existingUser = new User;
        $existingUser->fromArray([
            'email'    => 'existing@user.com',
            'password' => '12345'
        ]);

        $this->existingUser = $existingUser;
    }

    public function setUpMockUserService()
    {
        $this->setUpExistingUser();

        $existingUser = $this->existingUser;

        $this->mockUserService = $this->getMock('Synapse\User\UserService');
        $this->mockUserService->expects($this->any())
            ->method('findById')
            ->will($this->returnValue($existingUser));

        $captured = $this->captured;

        $this->mockUserService->expects($this->any())
            ->method('register')
            ->will($this->returnCallback(function($userValues) use ($existingUser, $captured) {
                if (Arr::get($userValues, 'email') === $existingUser->getEmail()) {
                    throw new OutOfBoundsException(
                        '',
                        UserService::EMAIL_NOT_UNIQUE
                    );
                }

                $newUserValues = $userValues;
                $newUserValues['id'] = 1;

                $user = new User;
                $user->fromArray($newUserValues);

                $captured->registeredUser = $user;

                return $user;
            }));

        $this->captured = $captured;
    }

    public function setUpMockUrlGenerator()
    {
        $captured = $this->captured;

        $this->mockUrlGenerator = $this->getMock(
            'Symfony\Component\Routing\Generator\UrlGeneratorInterface'
        );
        $this->mockUrlGenerator->expects($this->any())
            ->method('generate')
            ->will($this->returnCallback(function($name, $params, $refType) use ($captured) {
                $generatedUrl = '/users/'.Arr::get($params, 'id');

                $captured->generatedUrl = $generatedUrl;

                return $generatedUrl;
            }));

        $this->captured = $captured;
    }

    public function makeGetRequest()
    {
        $request = new Request(['id' => '1']);
        $request->setMethod('get');
        $request->headers->set('CONTENT_TYPE', 'application/json');

        return $this->userController->execute($request);
    }

    public function makePostRequestWithPasswordOnly()
    {
        $request = new Request([], [], [], [], [], [],
            json_encode(['password' => '12345'])
        );
        $request->setMethod('post');
        $request->headers->set('CONTENT_TYPE', 'application/json');

        return $this->userController->execute($request);
    }

    public function makePostRequestWithEmailOnly()
    {
        $request = new Request([], [], [], [], [], [],
            json_encode(['email' => 'posted@user.com'])
        );
        $request->setMethod('post');
        $request->headers->set('CONTENT_TYPE', 'application/json');

        return $this->userController->execute($request);
    }

    public function makePostRequestWithNonUniqueEmail()
    {
        $request = new Request([], [], [], [], [], [],
            json_encode([
                'email'    => $this->existingUser->getEmail(),
                'password' => '12345'
            ])
        );
        $request->setMethod('post');
        $request->headers->set('CONTENT_TYPE', 'application/json');

        return $this->userController->execute($request);
    }

    public function makeValidPostRequest()
    {
        $request = new Request([], [], [], [], [], [],
            json_encode([
                'email'    => 'posted@user.com',
                'password' => '12345'
            ])
        );
        $request->setMethod('post');
        $request->headers->set('CONTENT_TYPE', 'application/json');

        return $this->userController->execute($request);
    }

    public function testGetReturnsUserArrayWithoutThePassword()
    {
        $response = $this->makeGetRequest();

        $userArrayWithoutPassword = array_diff_key(
            $this->existingUser->getArrayCopy(),
            ['password' => '']
        );

        $this->assertEquals(
            $userArrayWithoutPassword,
            json_decode($response->getContent(), TRUE)
        );
    }

    public function testPostReturns422IfEmailIsMissing()
    {
        $response = $this->makePostRequestWithPasswordOnly();

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testPostReturns422IfPasswordIsMissing()
    {
        $response = $this->makePostRequestWithEmailOnly();

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testPostReturns409IfEmailExists()
    {
        $response = $this->makePostRequestWithNonUniqueEmail();

        $this->assertEquals(409, $response->getStatusCode());
    }

    public function testPostReturns201IfValid()
    {
        $response = $this->makeValidPostRequest();

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testPostReturnsResponseWithUserDataAndUrlForUserEndpoint()
    {
        $response = $this->makeValidPostRequest();

        $expected = $this->captured->registeredUser->getArrayCopy();
        unset($expected['password']);
        $expected['_href'] = $this->captured->generatedUrl;

        $this->assertEquals(
            $expected,
            json_decode($response->getContent(), TRUE)
        );
    }
}
