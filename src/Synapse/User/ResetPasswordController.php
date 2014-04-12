<?php

namespace Synapse\User;

use Symfony\Component\HttpFoundation\Request;
use Synapse\Controller\AbstractRestController;
use Synapse\User\TokenEntity;
use Synapse\Email\EmailService;
use Synapse\Stdlib\Arr;
use Synapse\View\Email\ResetPassword as ResetPasswordView;
use OutOfBoundsException;

/**
 * Controller for resetting passwords
 */
class ResetPasswordController extends AbstractRestController
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var EmailService
     */
    protected $emailService;

    /**
     * @var ResetPasswordView
     */
    protected $resetPasswordView;

    /**
     * @param UserService       $userService
     * @param EmailService      $emailService
     * @param ResetPasswordView $resetPasswordView
     */
    public function __construct(
        UserService $userService,
        EmailService $emailService,
        ResetPasswordView $resetPasswordView
    ) {
        $this->userService       = $userService;
        $this->emailService      = $emailService;
        $this->resetPasswordView = $resetPasswordView;
    }

    /**
     * Send reset password email
     *
     * @param  Request $request
     * @return array
     */
    public function post(Request $request)
    {
        // Validate user
        $email = Arr::get($this->content, 'email');
        $user  = $this->userService->findByEmail($email);

        if (! $user) {
            return $this->createNotFoundResponse();
        }

        // Create token and send email
        $userToken = $this->userService->createUserToken([
            'type'    => TokenEntity::TYPE_RESET_PASSWORD,
            'user_id' => $user->getId(),
        ]);

        $this->resetPasswordView->setUserToken($userToken);

        $email = $this->emailService->createFromArray([
            'recipient_email' => $user->getEmail(),
            'subject'         => 'Reset Your Password',
            'message'         => (string) $this->resetPasswordView,
        ]);

        $this->emailService->enqueueSendEmailJob($email);

        return $this->getSimpleResponse(204, '');
    }

    /**
     * Reset password using token and new password
     *
     * @param  Request $request
     * @return array
     */
    public function put(Request $request)
    {
        $token = Arr::get($this->content, 'token');

        $conditions = [
            'token'   => $token,
            'type'    => TokenEntity::TYPE_RESET_PASSWORD,
        ];

        // Ensure token is valid
        $token = $this->userService->findTokenBy($conditions);

        if (! $token) {
            return $this->getSimpleResponse(404, 'Token not found');
        }

        if ($token->getExpires() < time()) {
            return $this->getSimpleResponse(404, 'Token not found');
        }

        $user = $this->userService->findById($token->getUserId());

        if (! $user) {
            return $this->getSimpleResponse(404, 'User not found');
        }

        $password = Arr::get($this->content, 'password');

        // Ensure user input is valid
        if (! $password) {
            return $this->getSimpleResponse(422, 'Password cannot be empty');
        }

        $this->userService->resetPassword($user, $password);

        $this->userService->deleteToken($token);

        return $this->userArrayWithoutPassword($user);
    }

    /**
     * Transform the User entity into an array and remove the password element
     *
     * @param  User   $user
     * @return array
     */
    protected function userArrayWithoutPassword(UserEntity $user)
    {
        $user = $user->getArrayCopy();

        unset($user['password']);

        return $user;
    }
}
