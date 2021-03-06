<?php

namespace Synapse\User;

use Symfony\Component\HttpFoundation\Request;
use Synapse\Controller\AbstractRestController;
use Synapse\User\TokenEntity;
use Synapse\Stdlib\Arr;
use Synapse\Security\SecurityAwareInterface;
use Synapse\Security\SecurityAwareTrait;
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
        $token = Arr::get($this->getContentAsArray($request), 'token');

        if (! $token) {
            return $this->createSimpleResponse(422, 'Token not specified.');
        }

        $conditions = [
            'user_id'       => $id,
            'token'         => $token,
            'token_type_id' => TokenEntity::TYPE_VERIFY_REGISTRATION,
        ];

        $token = $this->userService->findTokenBy($conditions);

        if (! $token) {
            return $this->createNotFoundResponse();
        }

        try {
            $user = $this->userService->verifyRegistration($token);
        } catch (OutOfBoundsException $e) {
            $httpCodes = [
                UserService::INCORRECT_TOKEN_TYPE => 422,
                UserService::TOKEN_EXPIRED        => 410,
                UserService::TOKEN_NOT_FOUND      => 404,
            ];

            return $this->createErrorResponse(
                ['token' => ['INVALID']],
                $httpCodes[$e->getCode()]
            );
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
