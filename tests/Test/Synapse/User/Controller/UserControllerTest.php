<?php

namespace Test\Synapse\User\Controller;

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
    const EXISTING_USER_ID     = '1';
    const LOGGED_IN_USER_ID    = '2';
    const NON_EXISTENT_USER_ID = '3';

    public function setUp()
    {
        $this->captured = new stdClass();

        $this->userController = new UserController;

        $this->setUpMockUserService();
        $this->setUpMockUrlGenerator();
        $this->setUpMockSecurityContext();

        $this->userController->setUserService($this->mockUserService);
        $this->userController->setUrlGenerator($this->mockUrlGenerator);
        $this->userController->setSecurityContext($this->mockSecurityContext);
    }

    public function setUpExistingUser()
    {
        $existingUser = new User;
        $existingUser->fromArray([
            'id'       => self::EXISTING_USER_ID,
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
            ->will($this->returnCallback(function ($userId) use ($existingUser) {
                if ($userId === self::EXISTING_USER_ID) {
                    return $existingUser;
                } else {
                    return false;
                }
            }));

        $captured = $this->captured;

        $this->mockUserService->expects($this->any())
            ->method('register')
            ->will($this->returnCallback(function ($userValues) use ($existingUser, $captured) {
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
            ->will($this->returnCallback(function ($name, $params, $refType) use ($captured) {
                $generatedUrl = '/users/'.Arr::get($params, 'id');

                $captured->generatedUrl = $generatedUrl;

                return $generatedUrl;
            }));

        $this->captured = $captured;
    }

    public function setUpMockSecurityContext()
    {
        $captured = $this->captured;

        $this->mockSecurityContext = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSecurityToken = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $loggedInUserEntity = $this->getLoggedInUserEntity();

        $mockSecurityToken->expects($this->any())
            ->method('getUser')
            ->will($this->returnCallback(function () use ($loggedInUserEntity, $captured) {
                $captured->userReturnedFromSecurityContext = $loggedInUserEntity;

                return $loggedInUserEntity;
            }));

        $this->mockSecurityContext->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($mockSecurityToken));

        $this->captured = $captured;
    }

    public function getLoggedInUserEntity()
    {
        $user = new User();

        $user->fromArray([
            'id'    => self::LOGGED_IN_USER_ID,
            'email' => 'current@user.com'
        ]);

        return $user;
    }

    public function createJsonRequest($method, $params)
    {
        $this->request = new Request(
            Arr::get($params, 'getParams', []),
            [],
            Arr::get($params, 'attributes', []),
            [],
            [],
            [],
            Arr::get($params, 'content') ? json_encode($params['content']) : ''
        );
        $this->request->setMethod($method);
        $this->request->headers->set('CONTENT_TYPE', 'application/json');

        return $this->request;
    }

    public function makeGetRequestForUserId($userId)
    {
        $this->createJsonRequest('get', [
            'attributes' => ['id' => $userId]
        ]);

        return $this->userController->execute($this->request);
    }

    public function makePostRequestWithPasswordOnly()
    {
        $this->createJsonRequest('post', [
            'content' => ['password' => '12345']
        ]);

        return $this->userController->execute($this->request);
    }

    public function makePostRequestWithEmailOnly()
    {
        $this->createJsonRequest('post', [
            'content' => ['email' => 'posted@user.com']
        ]);

        return $this->userController->execute($this->request);
    }

    public function makePostRequestWithNonUniqueEmail()
    {
        $this->createJsonRequest('post', [
            'content' => [
                'email'    => $this->existingUser->getEmail(),
                'password' => '12345'
            ]
        ]);

        return $this->userController->execute($this->request);
    }

    public function makeValidPostRequest()
    {
        $this->createJsonRequest('post', [
            'content' => [
                'email'    => 'posted@user.com',
                'password' => '12345'
            ]
        ]);

        return $this->userController->execute($this->request);
    }

    public function makePutRequestForNonLoggedInUser()
    {
        $this->createJsonRequest('put', [
            'attributes' => ['id' => self::EXISTING_USER_ID],
            'content'    => ['email' => 'new@email.com']
        ]);

        return $this->userController->execute($this->request);
    }

    public function makeValidPutRequest()
    {
        $this->createJsonRequest('put', [
            'attributes' => ['id' => self::LOGGED_IN_USER_ID],
            'content' => [
                'email'    => 'new@email.com',
                'password' => '12345'
            ]
        ]);

        return $this->userController->execute($this->request);
    }

    public function withUserUpdateThrowingExceptionWithCode($code)
    {
        $this->mockUserService->expects($this->once())
            ->method('update')
            ->will($this->throwException(new OutOfBoundsException('', $code)));
    }

    public function withExpectedUserUpdate()
    {
        $captured = $this->captured;

        $this->mockUserService->expects($this->once())
            ->method('update')
            ->will($this->returnCallback(function ($user, $values) use ($captured) {
                $captured->updatedUser = $user;
                $captured->newUserValues = $values;

                $user->exchangeArray($values);

                return $user;
            }));

        $this->captured = $captured;
    }

    public function testGetReturnsUserArrayWithoutThePassword()
    {
        $response = $this->makeGetRequestForUserId(self::EXISTING_USER_ID);

        $userArrayWithoutPassword = array_diff_key(
            $this->existingUser->getArrayCopy(),
            ['password' => '']
        );

        $this->assertEquals(
            $userArrayWithoutPassword,
            json_decode($response->getContent(), true)
        );
    }

    public function testGetReturns404IfUserNotFound()
    {
        $response = $this->makeGetRequestForUserId(self::NON_EXISTENT_USER_ID);

        $this->assertEquals(
            404,
            $response->getStatusCode()
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
            json_decode($response->getContent(), true)
        );
    }

    public function testPutReturns403IfIdDoesNotMatchIdOfLoggedInUser()
    {
        $response = $this->makePutRequestForNonLoggedInUser();

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testPutReturns403IfOutOfBoundsExceptionThrownWithPasswordRequiredErrorCode()
    {
        $this->withUserUpdateThrowingExceptionWithCode(
            UserService::CURRENT_PASSWORD_REQUIRED
        );

        $response = $this->makeValidPutRequest();

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testPutReturns422IfOutOfBoundsExceptionThrownWithEmptyFieldErrorCode()
    {
        $this->withUserUpdateThrowingExceptionWithCode(
            UserService::FIELD_CANNOT_BE_EMPTY
        );

        $response = $this->makeValidPutRequest();

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testPutUpdatesUserWithNewData()
    {
        $this->withExpectedUserUpdate();

        $response = $this->makeValidPutRequest();

        $updatedUser = $this->captured->updatedUser;

        $this->assertEquals(
            $this->captured->userReturnedFromSecurityContext,
            $this->captured->updatedUser
        );
        $this->assertEquals(
            json_decode($this->request->getContent(), true),
            $this->captured->newUserValues
        );
    }

    public function testPutReturnsUserDataMinusPassword()
    {
        $this->withExpectedUserUpdate();

        $response = $this->makeValidPutRequest();

        $expected = $this->captured->userReturnedFromSecurityContext->getArrayCopy();
        unset($expected['password']);

        $this->assertEquals($expected, json_decode($response->getContent(), true));
    }

    public function testValidPutReturns200()
    {
        $this->withExpectedUserUpdate();

        $response = $this->makeValidPutRequest();

        $this->assertEquals(200, $response->getStatusCode());
    }
}
