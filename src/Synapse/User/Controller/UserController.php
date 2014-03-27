<?php

namespace Synapse\User\Controller;

use Symfony\Component\HttpFoundation\Request;
use Synapse\Controller\AbstractRestController;
use Synapse\User\Entity\User;
use Synapse\User\UserService;
use Synapse\Application\SecurityAwareInterface;
use Synapse\Application\SecurityAwareTrait;
use OutOfBoundsException;

/**
 * Controller for user related actions
 */
class UserController extends AbstractRestController implements SecurityAwareInterface
{
    use SecurityAwareTrait;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * Return a user entity
     *
     * @param  Request $request
     * @return array
     */
    public function get(Request $request)
    {
        $id = $request->attributes->get('id');

        $user = $this->userService
            ->findById($id);

        if (! $user) {
            return $this->getSimpleResponse(404, 'User not found');
        }

        return $this->userArrayWithoutPassword($user);
    }

    /**
     * Create a user
     *
     * @param  Request $request
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function post(Request $request)
    {
        $user = $this->content;

        if (! isset($user['email'], $user['password'])) {
            return $this->getSimpleResponse(422, 'Missing required field');
        }

        try {
            $newUser = $this->userService->register($user);
        } catch (OutOfBoundsException $e) {
            $httpCodes = [
                UserService::EMAIL_NOT_UNIQUE => 409,
            ];

            return $this->getSimpleResponse($httpCodes[$e->getCode()], $e->getMessage());
        }

        $newUser = $this->userArrayWithoutPassword($newUser);

        $newUser['_href'] = $this->url('user-entity', array('id' => $newUser['id']));

        $response = $this->getSimpleResponse(201, json_encode($newUser));
        return $response;
    }

    /**
     * Edit a user; requires the user to be logged in and the current password provided
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

        try {
            $user = $this->userService->update($user, $this->content);
        } catch (OutOfBoundsException $e) {
            $httpCodes = [
                UserService::CURRENT_PASSWORD_REQUIRED => 403,
                UserService::FIELD_CANNOT_BE_EMPTY     => 422,
            ];

            return $this->getSimpleResponse($httpCodes[$e->getCode()], $e->getMessage());
        }

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
