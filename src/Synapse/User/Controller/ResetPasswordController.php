<?php

namespace Synapse\User\Controller;

use Symfony\Component\HttpFoundation\Request;
use Synapse\Controller\AbstractRestController;
use Synapse\User\UserService;
use Synapse\User\Entity\UserToken;
use Synapse\User\Entity\User;
use Synapse\Stdlib\Arr;
use OutOfBoundsException;
use Synapse\Application\SecurityAwareInterface;
use Synapse\Application\SecurityAwareTrait;

/**
 * Controller for resetting passwords
 */
class ResetPasswordController extends AbstractRestController implements SecurityAwareInterface
{
    use SecurityAwareTrait;

    /**
     * @var Synapse\User\UserService
     */
    protected $userService;

    /**
     * Sending reset password email
     *
     * @param  Request $request
     * @return array
     */
    public function post(Request $request)
    {
        $user = $this->user();

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
        $user = $this->user();

        // Ensure the user in question is logged in
        if ($request->attributes->get('id') !== $user->getId()) {
            return $this->getSimpleResponse(403, 'Access denied');
        }

        $token = Arr::get($this->content, 'token');

        $conditions = [
            'user_id' => $user->getId(),
            'token'   => $token,
            'type'    => UserToken::TYPE_RESET_PASSWORD,
        ];

        // Ensure token is valid
        $token = $this->userService->findTokenBy($conditions);

        if (! $token) {
            return $this->getSimpleResponse(404, 'Token not found');
        }

        $currentPassword = Arr::get($this->content, 'current_password');
        $password        = Arr::get($this->content, 'password');

        // Ensure user input is valid
        if (! $password) {
            return $this->getSimpleResponse(422, 'Password cannot be empty');
        }

        if (! password_verify($currentPassword, $user->getPassword())) {
            return $this->getSimpleResponse(403, 'Current password incorrect');
        }

        $user = $this->userService->resetPassword($user, $password);

        $this->userService->deleteToken($token);

        return $this->userArrayWithoutPassword($user);
    }

    /**
     * @param UserService $service
     */
    public function setUserService(UserService $service)
    {
        $this->userService = $service;
        return $this;
    }

    /**
     * Transform the User entity into an array and remove the password element
     *
     * @param  User   $user
     * @return array
     */
    protected function userArrayWithoutPassword(User $user)
    {
        $user = $user->getArrayCopy();

        unset($user['password']);

        return $user;
    }
}
