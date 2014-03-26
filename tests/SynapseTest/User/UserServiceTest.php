<?php

namespace SynapseTest\User;

use PHPUnit_Framework_TestCase;
use Synapse\User\UserService;
use Synapse\User\Entity\User as UserEntity;
use Synapse\User\Entity\UserToken as UserToken;
use Synapse\Email\Entity\Email;

class UserServiceTest extends PHPUnit_Framework_TestCase
{
    const CURRENT_PASSWORD = '12345';
    const VERIFY_REGISTRATION_VIEW_STRING_VALUE = 'verify_registration';
    const RESET_PASSWORD_VIEW_STRING_VALUE = 'reset_password';

    public function setUp()
    {
        $this->userService = new UserService();

        $this->createMocks();

        $this->userService->setUserMapper($this->mockUserMapper);
        $this->userService->setUserTokenMapper($this->mockUserTokenMapper);
        $this->userService->setVerifyRegistrationView($this->mockVerifyRegistrationView);
        $this->userService->setResetPasswordView($this->mockResetPasswordView);
        $this->userService->setEmailService($this->mockEmailService);
    }

    public function createMocks()
    {
        $this->mockUserMapper = $this->getMockBuilder('Synapse\User\Mapper\User')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockUserTokenMapper = $this->getMockBuilder('Synapse\User\Mapper\UserToken')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockUserTokenMapper->expects($this->any())
            ->method('persist')
            ->will($this->returnCallback(function($entity) {
                return $entity;
            }));

        $this->mockVerifyRegistrationView = $this->getMockBuilder('Synapse\View\Email\VerifyRegistration')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockVerifyRegistrationView->expects($this->any())
            ->method('__toString')
            ->will($this->returnValue(self::VERIFY_REGISTRATION_VIEW_STRING_VALUE));

        $this->mockResetPasswordView = $this->getMockBuilder('Synapse\View\Email\ResetPassword')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockResetPasswordView->expects($this->any())
            ->method('__toString')
            ->will($this->returnValue(self::RESET_PASSWORD_VIEW_STRING_VALUE));

        $this->mockEmailService = $this->getMock('Synapse\Email\EmailService');
    }

    public function getCurrentPasswordHash()
    {
        return password_hash(self::CURRENT_PASSWORD, PASSWORD_BCRYPT);
    }

    public function getUserEntity()
    {
        $user = new UserEntity();

        $user->fromArray([
            'email'    => 'user@domain.com',
            'password' => $this->getCurrentPasswordHash()
        ]);

        return $user;
    }

    public function withExistingUser()
    {
        $email = 'existing@user.com';
        $id    = 'existing_user_id';

        $user = new UserEntity();
        $user->fromArray([
            'id'    => $id,
            'email' => $email
        ]);

        $this->mockUserMapper->expects($this->any())
            ->method('findByEmail')
            ->with($this->equalTo($email))
            ->will($this->returnValue($user));

        $this->mockUserMapper->expects($this->any())
            ->method('findById')
            ->with($this->equalTo($id))
            ->will($this->returnValue($user));

        return $user;
    }

    public function withNoExistingUser()
    {
        $this->mockUserMapper->expects($this->any())
            ->method('findByEmail')
            ->with($this->anything())
            ->will($this->returnValue(false));
    }

    public function expectingPersistedUserEntity()
    {
        $captured = new \stdClass();

        $this->mockUserMapper->expects($this->once())
            ->method('persist')
            ->will($this->returnCallback(function($userEntity) use ($captured) {
                $captured->persistedUserEntity = $userEntity;
                return $userEntity;
            }));

        return $captured;
    }

    public function expectingPersistedUserToken()
    {
        $captured = new \stdClass();

        $this->mockUserTokenMapper->expects($this->once())
            ->method('persist')
            ->will($this->returnCallback(function($userTokenEntity) use ($captured) {
                $captured->persistedUserTokenEntity = $userTokenEntity;
                return $userTokenEntity;
            }));

        return $captured;
    }

    public function expectingEmailCreatedFromArray()
    {
        $captured = new \stdClass();

        $this->mockEmailService->expects($this->once())
            ->method('createFromArray')
            ->will($this->returnCallback(function($array) use ($captured) {
                $captured->emailArray = $array;

                $email = new Email;
                $email->fromArray($array);

                $captured->createdEmailEntity = $email;

                return $email;
            }));

        return $captured;
    }

    public function expectingDeletedToken($token)
    {
        $this->mockUserTokenMapper->expects($this->once())
            ->method('delete')
            ->with($this->identicalTo($token));
    }

    public function expectingEmailEnqueued()
    {
        $captured = new \stdClass();

        $this->mockEmailService->expects($this->once())
            ->method('enqueueSendEmailJob')
            ->will($this->returnCallback(function($entity) use ($captured) {
                $captured->sentEmailEntity = $entity;
            }));

        return $captured;
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testExceptionThrownIfAttemptingToSetEmailWithoutSpecifyingCurrentPassword()
    {
        $this->userService->update(
            $this->getUserEntity(),
            ['email' => 'new@email.com']
        );
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testExceptionThrownIfAttemptingToSetPasswordWithoutSpecifyingCurrentPassword()
    {
        $this->userService->update(
            $this->getUserEntity(),
            ['password' => 'new_password']
        );
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testExceptionThrownIfAttemptingToSetEmptyEmail()
    {
        $this->userService->update(
            $this->getUserEntity(),
            [
                'current_password' => self::CURRENT_PASSWORD,
                'email'            => ''
            ]
        );
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testExceptionThrownIfAttemptingToSetEmptyPassword()
    {
        $this->userService->update(
            $this->getUserEntity(),
            [
                'current_password' => self::CURRENT_PASSWORD,
                'password'         => ''
            ]
        );
    }

    public function testUpdateChangesUserEntityValues()
    {
        $user = $this->getUserEntity();

        $this->userService->update(
            $user,
            [
                'current_password' => self::CURRENT_PASSWORD,
                'password'         => 'new_password',
                'email'            => 'new@email.com',
            ]
        );

        $this->assertTrue(password_verify('new_password', $user->getPassword()));
        $this->assertEquals('new@email.com', $user->getEmail());
    }

    public function testUpdatePassesUserEntityToUpdateMethodOfMapperAndReturnsResult()
    {
        $user = $this->getUserEntity();

        $this->mockUserMapper->expects($this->once())
            ->method('update')
            ->with($this->equalTo($user))
            ->will($this->returnValue('returnValue'));

        $returnValue = $this->userService->update(
            $user,
            [
                'current_password' => self::CURRENT_PASSWORD,
                'password'         => 'new_password',
                'email'            => 'new@email.com',
            ]
        );

        $this->assertEquals('returnValue', $returnValue);
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testRegisterThrowsExceptionIfUserWIthEmailExists()
    {
        $user = $this->withExistingUser();

        $this->userService->register([
            'email'    => $user->getEmail(),
            'password' => 'password'
        ]);
    }

    public function testRegisterPersistsUserDataToMapper()
    {
        $this->withNoExistingUser();
        $captured = $this->expectingPersistedUserEntity();
        $this->expectingEmailCreatedFromArray();

        $this->userService->register([
            'email'    => 'new@email.com',
            'password' => 'password'
        ]);

        $this->assertEquals($captured->persistedUserEntity->getEmail(), 'new@email.com');
        $this->assertTrue(password_verify('password', $captured->persistedUserEntity->getPassword()));
    }

    public function testRegisterEnqueuesVerifyRegistrationEmail()
    {
        $this->withNoExistingUser();
        $this->expectingPersistedUserEntity();
        $capturedEmailCreation = $this->expectingEmailCreatedFromArray();
        $capturedEmailSending = $this->expectingEmailEnqueued();

        $this->userService->register([
            'email'    => 'new@email.com',
            'password' => 'password'
        ]);

        $this->assertSame(
            $capturedEmailCreation->createdEmailEntity,
            $capturedEmailSending->sentEmailEntity
        );
        $this->assertSame(
            self::VERIFY_REGISTRATION_VIEW_STRING_VALUE,
            $capturedEmailCreation->emailArray['message']
        );
    }

    public function testRegisterWithoutPasswordPersistsUserEntity()
    {
        $captured = $this->expectingPersistedUserEntity();

        $this->userService->registerWithoutPassword([
            'email'    => 'new@email.com',
        ]);

        $this->assertEquals($captured->persistedUserEntity->getEmail(), 'new@email.com');
    }

    public function testSendResetPasswordEmailEnqueusResetPasswordEmail()
    {
        $this->expectingPersistedUserToken();
        $capturedEmailCreation = $this->expectingEmailCreatedFromArray();
        $capturedEmailSending = $this->expectingEmailEnqueued();

        $this->userService->sendResetPasswordEmail($this->getUserEntity());

        $this->assertSame(
            self::RESET_PASSWORD_VIEW_STRING_VALUE,
            $capturedEmailCreation->emailArray['message']
        );
        $this->assertSame(
            $capturedEmailCreation->createdEmailEntity,
            $capturedEmailSending->sentEmailEntity
        );
    }

    public function testResetPasswordPersistsUserWithHashedPasswordSet()
    {
        $user = $this->getUserEntity();
        $this->expectingPersistedUserEntity();

        $this->userService->resetPassword(
            $user,
            'reset_password'
        );

        $this->assertTrue(
            password_verify(
                'reset_password',
                $user->getPassword()
            )
        );
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testVerifyRegistrationThrowsExceptionIfTokenNotFound()
    {
        $this->userService->verifyRegistration(new UserToken);
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testVerifyRegistrationThrowsExceptionIfTokenIsOfWrongType()
    {
        $userToken = new UserToken();
        $userToken->fromArray([
            'id'   => '1',
            'type' => UserToken::TYPE_RESET_PASSWORD
        ]);

        $this->userService->verifyRegistration($userToken);
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testVerifyRegistrationThrowsExceptionIfExpireTimeHasPassed()
    {
        $userToken = new UserToken();
        $userToken->fromArray([
            'id'      => '1',
            'type'    => UserToken::TYPE_VERIFY_REGISTRATION,
            'expires' => time() - 1000
        ]);

        $this->userService->verifyRegistration($userToken);
    }

    public function testVerifyRegistrationPersistsVerifiedUserAndDeletesToken()
    {
        $captured = $this->expectingPersistedUserEntity();
        $user = $this->withExistingUser();

        $userToken = new UserToken();
        $userToken->fromArray([
            'id'      => '1',
            'type'    => UserToken::TYPE_VERIFY_REGISTRATION,
            'expires' => time() + 1000,
            'user_id' => $user->getId()
        ]);
        $this->expectingDeletedToken($userToken);

        $returnValue = $this->userService->verifyRegistration($userToken);

        $this->assertTrue($captured->persistedUserEntity->getVerified());
        $this->assertSame($user, $returnValue);
    }
}
