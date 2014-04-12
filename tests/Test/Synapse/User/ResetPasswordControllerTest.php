<?php

namespace Test\Synapse\User;

use Symfony\Component\HttpFoundation\Request;
use Synapse\TestHelper\ControllerTestCase;
use Synapse\User\ResetPasswordController;
use Synapse\Email\EmailEntity;
use Synapse\User\UserEntity;
use Synapse\User\TokenEntity;

class ResetPasswordControllerTest extends ControllerTestCase
{
    const VERIFY_REGISTRATION_VIEW_STRING_VALUE = 'verify_registration';
    const ACCOUNT_EMAIL_TO_RESET                = 'account@example.com';
    const TOKEN_VALUE                           = 'abcdefg1234567';

    public $newPassword = 'passw0rd';

    public function setUp()
    {
        $this->setUpMockUserService();
        $this->setUpMockEmailService();
        $this->setUpMockResetPasswordView();

        $this->controller = new ResetPasswordController(
            $this->mockUserService,
            $this->mockEmailService,
            $this->mockResetPasswordView
        );
    }

    public function setUpMockUserService()
    {
        $this->mockUserService = $this->getMockBuilder('Synapse\User\UserService')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function setUpMockEmailService()
    {
        $this->mockEmailService = $this->getMockBuilder('Synapse\Email\EmailService')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function setUpMockResetPasswordView()
    {
        $mockResetPasswordView = $this->getMockBuilder('Synapse\View\Email\ResetPassword')
            ->disableOriginalConstructor()
            ->getMock();

        $mockResetPasswordView->expects($this->any())
            ->method('__toString')
            ->will($this->returnValue(self::VERIFY_REGISTRATION_VIEW_STRING_VALUE));

        $this->mockResetPasswordView = $mockResetPasswordView;
    }

    public function createUserEntity()
    {
        $entity = new UserEntity();

        $entity = $entity->exchangeArray([
            'id'         => 213,
            'email'      => 'user@example.com',
            'password'   => 'password',
            'last_login' => 123456789,
            'created'    => 987654321,
            'enabled'    => 1,
            'verified'   => 1,
        ]);

        return $entity;
    }

    public function createEmailEntity()
    {
        $entity = new EmailEntity();

        $entity = $entity->exchangeArray([
            'id'              => 20,
            'subject'         => 'Subject!',
            'recipient_email' => 'recipient@example.com',
            'sender_email'    => 'test@example.com',
            'message'         => 'Message!',
        ]);

        return $entity;
    }

    public function createTokenEntity($expires = null)
    {
        if ($expires === null) {
            $expires = time()+1000;
        }

        $entity = new TokenEntity();

        $entity = $entity->exchangeArray([
            'id'      => 10,
            'user_id' => 11,
            'token'   => self::TOKEN_VALUE,
            'type'    => TokenEntity::TYPE_RESET_PASSWORD,
            'created' => time()-1000,
            'expires' => $expires,
        ]);

        return $entity;
    }

    public function expectingFindByEmailCalledOnUserServiceWithEmail()
    {
        $userEntity = $this->createUserEntity();

        $this->mockUserService->expects($this->once())
            ->method('findByEmail')
            ->with(self::ACCOUNT_EMAIL_TO_RESET)
            ->will($this->returnValue($userEntity));
    }

    public function expectingCreateFromArrayCalledOnEmailService()
    {
        $message = (string) $this->mockResetPasswordView;

        $argument = [
            'recipient_email' => $this->createUserEntity()->getEmail(),
            'subject'         => 'Reset Your Password',
            'message'         => $message,
        ];

        $this->mockEmailService->expects($this->once())
            ->method('createFromArray')
            ->with($argument);
    }

    public function expectingCreateUserTokenCalledOnUserService()
    {
        $this->mockUserService->expects($this->once())
            ->method('createUserToken');
    }

    public function expectingEnqueueSendEmailJobCalledOnEmailService()
    {
        $this->mockEmailService->expects($this->once())
            ->method('enqueueSendEmailJob');
    }

    public function expectingDeleteTokenCalledOnUserService()
    {
        $token = $this->createTokenEntity();

        $this->mockUserService->expects($this->once())
            ->method('deleteToken')
            ->with($token);
    }

    public function expectingResetPasswordCalledOnUserService()
    {
        $user = $this->createUserEntity();

        $this->mockUserService->expects($this->once())
            ->method('resetPassword')
            ->with($user, $this->newPassword);
    }

    public function performPostRequest()
    {
        $content = ['email' => self::ACCOUNT_EMAIL_TO_RESET];

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            json_encode($content)
        );

        $request->setMethod('POST');

        return $this->controller->execute($request);
    }

    public function performPutRequest()
    {
        $content = [
            'token'    => self::ACCOUNT_EMAIL_TO_RESET,
            'password' => $this->newPassword
        ];

        if ($this->newPassword === null) {
            unset($content['password']);
        }

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            json_encode($content)
        );

        $request->setMethod('PUT');

        return $this->controller->execute($request);
    }

    public function withUserServiceFindByEmailReturningUser()
    {
        $user = $this->createUserEntity();

        $this->mockUserService->expects($this->any())
            ->method('findByEmail')
            ->will($this->returnValue($user));
    }

    public function withUserServiceFindByIdReturningUser()
    {
        $user = $this->createUserEntity();

        $this->mockUserService->expects($this->any())
            ->method('findById')
            ->will($this->returnValue($user));
    }

    public function withUserServiceFindByEmailReturningFalse()
    {
        $this->mockUserService->expects($this->any())
            ->method('findByEmail')
            ->will($this->returnValue(false));
    }

    public function withUserServiceCreateUserTokenReturningToken()
    {
        $token = $this->createTokenEntity();

        $this->mockUserService->expects($this->any())
            ->method('createUserToken')
            ->will($this->returnValue($token));
    }

    public function withEmailServiceCreateFromArrayReturningEntity()
    {
        $email = $this->createEmailEntity();

        $this->mockEmailService->expects($this->any())
            ->method('createFromArray')
            ->will($this->returnValue($email));
    }

    public function withUserServiceFindTokenByReturningToken()
    {
        $token = $this->createTokenEntity();

        $this->mockUserService->expects($this->any())
            ->method('findTokenBy')
            ->will($this->returnValue($token));
    }

    public function withUserServiceFindTokenByReturningFalse()
    {
        $this->mockUserService->expects($this->any())
            ->method('findTokenBy')
            ->will($this->returnValue(false));
    }

    public function withUserServiceFindTokenByReturningExpiredToken()
    {
        $expiration = time()-1000;
        $token      = $this->createTokenEntity($expiration);

        $this->mockUserService->expects($this->any())
            ->method('findTokenBy')
            ->will($this->returnValue($token));
    }

    public function withNewPasswordOmittedInRequest()
    {
        $this->newPassword = null;
    }

    public function testPostFindsUserByEmail()
    {
        $this->withUserServiceFindByEmailReturningUser();
        $this->withUserServiceCreateUserTokenReturningToken();
        $this->withEmailServiceCreateFromArrayReturningEntity();
        $this->expectingFindByEmailCalledOnUserServiceWithEmail();

        $this->performPostRequest();
    }

    public function testPostCreatesUserToken()
    {
        $this->withUserServiceCreateUserTokenReturningToken();
        $this->withEmailServiceCreateFromArrayReturningEntity();
        $this->withUserServiceFindByEmailReturningUser();

        $this->expectingCreateUserTokenCalledOnUserService();

        $this->performPostRequest();
    }

    public function testPostCreatesEmail()
    {
        $this->withEmailServiceCreateFromArrayReturningEntity();
        $this->withUserServiceFindByEmailReturningUser();
        $this->withUserServiceCreateUserTokenReturningToken();

        $this->expectingCreateFromArrayCalledOnEmailService();

        $this->performPostRequest();
    }

    public function testPostEnqueuesEmailJob()
    {
        $this->withEmailServiceCreateFromArrayReturningEntity();
        $this->withUserServiceFindByEmailReturningUser();
        $this->withUserServiceCreateUserTokenReturningToken();

        $this->expectingEnqueueSendEmailJobCalledOnEmailService();

        $this->performPostRequest();
    }

    public function testPostReturns204WithoutContent()
    {
        $this->withEmailServiceCreateFromArrayReturningEntity();
        $this->withUserServiceFindByEmailReturningUser();
        $this->withUserServiceCreateUserTokenReturningToken();

        $response = $this->performPostRequest();

        $this->assertEquals(
            204,
            $response->getStatusCode()
        );

        $this->assertEquals(
            '',
            $response->getContent()
        );
    }

    public function testPostReturns404IfAccountDoesNotExist()
    {
        $this->withUserServiceFindByEmailReturningFalse();

        $response = $this->performPostRequest();

        $this->assertEquals(
            404,
            $response->getStatusCode()
        );
    }

    public function testPutReturns404IfTokenNotFound()
    {
        $this->withUserServiceFindTokenByReturningFalse();

        $response = $this->performPutRequest();

        $this->assertEquals(
            404,
            $response->getStatusCode()
        );
    }

    public function testPutReturns404IfTokenExpired()
    {
        $this->withUserServiceFindTokenByReturningExpiredToken();

        $response = $this->performPutRequest();

        $this->assertEquals(
            404,
            $response->getStatusCode()
        );
    }

    public function testPutReturns422IfRequestDoesNotContainNewPassword()
    {
        $this->withNewPasswordOmittedInRequest();
        $this->withUserServiceFindTokenByReturningToken();
        $this->withUserServiceFindByIdReturningUser();

        $response = $this->performPutRequest();

        $this->assertEquals(
            422,
            $response->getStatusCode()
        );
    }

    public function testPutReturns200AndUserEntityWithPasswordRemoved()
    {
        $this->withUserServiceFindTokenByReturningToken();
        $this->withUserServiceFindByIdReturningUser();

        $response = $this->performPutRequest();

        $this->assertEquals(
            200,
            $response->getStatusCode()
        );
    }

    public function testPutResetsPassword()
    {
        $this->withUserServiceFindTokenByReturningToken();
        $this->withUserServiceFindByIdReturningUser();

        $this->expectingResetPasswordCalledOnUserService();

        $this->performPutRequest();
    }

    public function testPutDeletesToken()
    {
        $this->withUserServiceFindTokenByReturningToken();
        $this->withUserServiceFindByIdReturningUser();

        $this->expectingDeleteTokenCalledOnUserService();

        $this->performPutRequest();
    }
}
