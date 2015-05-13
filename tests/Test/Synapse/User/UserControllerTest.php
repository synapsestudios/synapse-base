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
        $this->setUpMockUserRegistrationValidator();
        $this->setUpMockUrlGenerator();
        $this->setUpMockSecurityContext();

        $this->userController->setUserService($this->mockUserService)
            ->setUserValidator($this->mockUserValidator)
            ->setUserRegistrationValidator($this->mockUserRegistrationValidator)
            ->setUrlGenerator($this->mockUrlGenerator);

        $this->injectMockSecurityContext($this->userController);

        $this->injectMockValidationErrorFormatter($this->userController);
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

    public function setUpMockUserRegistrationValidator()
    {
        $this->mockUserRegistrationValidator = $this->getMockBuilder('Synapse\User\UserRegistrationValidator')
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

    public function makeGetRequestForUser()
    {
        $this->createJsonRequest('get');

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

    public function makePutRequestWithNonUniqueEmail()
    {
        $this->createJsonRequest('put', [
            'attributes' => ['id' => self::LOGGED_IN_USER_ID],
            'content' => [
                'email'    => $this->existingUser->getEmail(),
                'password' => '12345'
            ]
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

    public function expectingUserUpdate()
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

    public function withRegistrationValidatorValidateReturningErrors()
    {
        $errors = $this->createNonEmptyConstraintViolationList();

        $this->mockUserRegistrationValidator->expects($this->any())
            ->method('validate')
            ->will($this->returnValue($errors));
    }

    public function testGetReturnsUserArrayWithoutThePassword()
    {
        $response = $this->makeGetRequestForUser();

        $userArrayWithoutPassword = array_diff_key(
            $this->getLoggedInUserEntity()->getArrayCopy(),
            ['password' => '']
        );

        $this->assertEquals(
            $userArrayWithoutPassword,
            json_decode($response->getContent(), true)
        );
    }

    public function testGetReturnsUserFoundWithoutThePasswordIfUserIsProvided()
    {
        $userEntity = $this->existingUser;

        $request = $this->createJsonRequest('GET', [
            'attributes' => [
                'user' => $userEntity
            ]
        ]);

        $response = $this->userController->get($request);

        $expectedArray = array_diff_key(
            $this->existingUser->getArrayCopy(),
            ['password' => '']
        );

        $this->assertSame($expectedArray, $response);
    }

    public function testGetReturns404WhenUserProvidedDoesntExist()
    {
        $request = $this->createJsonRequest('GET', [
            'attributes' => [
                'user' => false
            ]
        ]);

        $response = $this->userController->get($request);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testPostReturns422IfValidationConstraintsAreViolated()
    {
        $this->withRegistrationValidatorValidateReturningErrors();
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

    public function testPutReturns422IfValidationConstraintsAreViolated()
    {
        $this->withValidatorValidateReturningErrors();

        $response = $this->makePutRequest();

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testPutReturns409IfEmailExists()
    {
        $this->withUserUpdateThrowingExceptionWithCode(
            UserService::EMAIL_NOT_UNIQUE
        );

        $response = $this->makePutRequestWithNonUniqueEmail();

        $this->assertEquals(409, $response->getStatusCode());
    }

    public function testPutUpdatesUserWithNewData()
    {
        $this->expectingUserUpdate();

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
        $this->expectingUserUpdate();

        $response = $this->makePutRequest();

        $expected = $this->captured->userReturnedFromSecurityContext->getArrayCopy();
        unset($expected['password']);

        $this->assertEquals($expected, json_decode($response->getContent(), true));
    }

    public function testValidPutReturns200()
    {
        $this->expectingUserUpdate();

        $response = $this->makePutRequest();

        $this->assertEquals(200, $response->getStatusCode());
    }
}
