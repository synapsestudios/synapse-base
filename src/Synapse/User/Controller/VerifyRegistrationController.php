<?php

namespace Synapse\User\Controller;

use Symfony\Component\HttpFoundation\Request;
use Synapse\Controller\AbstractRestController;
use Synapse\User\UserService;
use Synapse\User\Entity\UserToken;
use Synapse\Stdlib\Arr;
use Synapse\Application\SecurityAwareInterface;
use Synapse\Application\SecurityAwareTrait;
use OutOfBoundsException;

/**
 * Verify user registration
 */
class VerifyRegistrationController extends AbstractRestController implements SecurityAwareInterface
{
    use SecurityAwareTrait;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * Verify user registration with token and user id
     *
     * @param  Request $request
     * @return array
     */
    public function post(Request $request)
    {
        $id    = $request->attributes->get('id');
        $token = Arr::get($this->content, 'token');

        if (! $token) {
            return $this->getSimpleResponse(422, 'Token not specified.');
        }

        $conditions = [
            'user_id' => $id,
            'token'   => $token,
            'type'    => UserToken::TYPE_VERIFY_REGISTRATION,
        ];

        $token = $this->userService->findTokenBy($conditions);

        if (! $token) {
            return $this->getSimpleResponse(404, 'Token not found.');
        }

        try {
            $user = $this->userService->verifyRegistration($token);
        } catch (OutOfBoundsException $e) {
            $httpCodes = [
                UserService::INCORRECT_TOKEN_TYPE => 422,
                UserService::TOKEN_EXPIRED        => 410,
                UserService::TOKEN_NOT_FOUND      => 404,
            ];

            return $this->getSimpleResponse($httpCodes[$e->getCode()], $e->getMessage());
        }

        $user = $user->getArrayCopy();

        unset($user['password']);

        return $user;
    }

    /**
     * @param UserService $service
     */
    public function setUserService(UserService $service)
    {
        $this->userService = $service;
        return $this;
    }
}
