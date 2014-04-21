<?php

namespace Test\Synapse\User;

use OutOfBoundsException;
use stdClass;
use Synapse\Stdlib\Arr;
use Synapse\TestHelper\ControllerTestCase;
use Synapse\User\UserController;
use Synapse\User\UserEntity;
use Synapse\User\UserService;

class UserControllerTest extends ControllerTestCase
{
    const EXISTING_USER_ID     = '1';
    const LOGGED_IN_USER_ID    = '2';
    const NON_EXISTENT_USER_ID = '3';

    public function setUp()
    {
        $this->captured = new stdClass();

        $this->userController = new UserController;

        $this->setUpMockUserService();
        $this->setUpMockUserValidator();
        $this->setUpMockUrlGenerator();
        $this->setUpMockSecurityContext();

        $this->userController->setUserService($this->mockUserService)
            ->setUserValidator($this->mockUserValidator)
            ->setUrlGenerator($this->mockUrlGenerator)
            ->setSecurityContext($this->mockSecurityContext);
    }

    public function setUpExistingUser()
    {
        $existingUser = new UserEntity();
        $existingUser->exchangeArray([
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

                $user = new UserEntity();
                $user->exchangeArray($newUserValues);

                $captured->registeredUser = $user;

                return $user;
            }));

        $this->captured = $captured;
    }

    public function setUpMockUserValidator()
    {
        $this->mockUserValidator = $this->getMockBuilder('Synapse\User\UserValidator')
            ->disableOriginalConstructor()
            ->getMock();
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

    public function getLoggedInUserEntity()
    {
        $user = new UserEntity();

        $user->exchangeArray([
            'id'    => self::LOGGED_IN_USER_ID,
            'email' => 'current@user.com'
        ]);

        return $user;
    }

    public function makeGetRequestForUserId($userId)
    {
        $this->createJsonRequest('get', [
            'attributes' => ['id' => $userId]
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

    public function makePostRequest()
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

    public function makePutRequest()
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

    public function withValidatorValidateReturningErrors()
    {
        $errors = $this->createNonEmptyConstraintViolationList();

        $this->mockUserValidator->expects($this->any())
            ->method('validate')
            ->will($this->returnValue($errors));
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

    public function testPostReturns422IfValidationConstraintsAreViolated()
    {
        $this->withValidatorValidateReturningErrors();
        $response = $this->makePostRequest();

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testPostReturns409IfEmailExists()
    {
        $response = $this->makePostRequestWithNonUniqueEmail();

        $this->assertEquals(409, $response->getStatusCode());
    }

    public function testPostReturns201IfValid()
    {
        $response = $this->makePostRequest();

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testPostReturnsResponseWithUserDataAndUrlForUserEndpoint()
    {
        $response = $this->makePostRequest();

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

        $response = $this->makePutRequest();

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testPutReturns422IfOutOfBoundsExceptionThrownWithEmptyFieldErrorCode()
    {
        $this->withUserUpdateThrowingExceptionWithCode(
            UserService::FIELD_CANNOT_BE_EMPTY
        );

        $response = $this->makePutRequest();

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testPutReturns422IfValidationConstraintsAreViolated()
    {
        $this->withValidatorValidateReturningErrors();

        $response = $this->makePutRequest();

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testPutUpdatesUserWithNewData()
    {
        $this->withExpectedUserUpdate();

        $response = $this->makePutRequest();

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

        $response = $this->makePutRequest();

        $expected = $this->captured->userReturnedFromSecurityContext->getArrayCopy();
        unset($expected['password']);

        $this->assertEquals($expected, json_decode($response->getContent(), true));
    }

    public function testValidPutReturns200()
    {
        $this->withExpectedUserUpdate();

        $response = $this->makePutRequest();

        $this->assertEquals(200, $response->getStatusCode());
    }
}
