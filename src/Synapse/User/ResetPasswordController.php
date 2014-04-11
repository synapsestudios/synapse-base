<?php

namespace Synapse\User;

use Symfony\Component\HttpFoundation\Request;
use Synapse\Controller\AbstractRestController;
use Synapse\User\TokenEntity;
use Synapse\Stdlib\Arr;
use OutOfBoundsException;

/**
 * Controller for resetting passwords
 */
class ResetPasswordController extends AbstractRestController
{
    /**
     * @var Synapse\User\UserService
     */
    protected $userService;

    /**
     * @param UserService $service
     */
    public function __construct(UserService $service)
    {
        $this->userService = $service;
    }

    /**
     * Sending reset password email
     *
     * @param  Request $request
     * @return array
     */
    public function post(Request $request)
    {
        $email = Arr::get($this->content, 'email');
        $user  = $this->userService->findByEmail($email);

        // Ensure the user in question is logged in
        if ($request->attributes->get('id') !== $user->getId()) {
            return $this->getSimpleResponse(403, 'Access denied');
        }

        $user = $this->userService->sendResetPasswordEmail($user);

        return $this->userArrayWithoutPassword($user);
    }

    /**
     * Reset password using token and new password
     *
     * @param  Request $request
     * @return array
     */
    public function put(Request $request)
    {
        // Ensure the user in question is logged in
        if ($request->attributes->get('id') !== $user->getId()) {
            return $this->getSimpleResponse(403, 'Access denied');
        }

        $token = Arr::get($this->content, 'token');

        $conditions = [
            'user_id' => $user->getId(),
            'token'   => $token,
            'type'    => TokenEntity::TYPE_RESET_PASSWORD,
        ];

        // Ensure token is valid
        $token = $this->userService->findTokenBy($conditions);

        if (! $token) {
            return $this->getSimpleResponse(404, 'Token not found');
        }

        $password = Arr::get($this->content, 'password');

        // Ensure user input is valid
        if (! $password) {
            return $this->getSimpleResponse(422, 'Password cannot be empty');
        }

        $user = $this->userService->resetPassword($user, $password);

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
