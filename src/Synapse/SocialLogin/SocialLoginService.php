<?php

namespace Synapse\SocialLogin;

use Synapse\User\Entity\User as UserEntity;
use Synapse\User\UserService as UserService;
use Synapse\OAuth2\ResponseType\AccessToken;
use Synapse\OAuth2\Storage\ZendDb as OAuth2ZendDb;
use Synapse\SocialLogin\Exception\NoLinkedAccountException;
use Synapse\SocialLogin\Exception\LinkedAccountExistsException;
use OutOfBoundsException;

/**
 * Generic service for social login related tasks
 */
class SocialLoginService
{
    /**
     * Constant for account not found exceptions
     */
    const EXCEPTION_ACCOUNT_NOT_FOUND = 1;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var SocialLoginMapper
     */
    protected $socialLoginMapper;

    /**
     * @var OAuth2ZendDb
     */
    protected $tokenStorage;

    /**
     * Handle a request to log in via a social login provider
     *
     * @param  LoginRequest $request
     * @return AccessToken
     */
    public function handleLoginRequest(LoginRequest $request)
    {
        $socialLogin = $this->socialLoginMapper->findByProviderUserId(
            $request->getProvider(),
            $request->getProviderUserId()
        );

        if ($socialLogin) {
            $user = $this->userService->findById(
                $socialLogin->getUserId()
            );

            return $this->handleLogin($user, $request);
        }

        $userFound = false;
        foreach ($request->getEmails() as $email) {
            $user = $this->userService->findByEmail($email);

            if ($user) {
                $userFound = true;
                if ($this->userHasSocialLoginWithProvider($request->getProvider(), $user)) {
                    return $this->handleLogin($user, $request);
                }
            }
        }

        if ($userFound) {
            throw new NoLinkedAccountException;
        }

        $result = $this->registerFromSocialLogin($request);

        return $this->handleLogin($result['user']);
    }

    /**
     * Handle a request to link a social account to a non-social account
     *
     * @param  LoginRequest $request
     * @param  string       $userId  ID of the non-social account to link with the social account
     * @return AccessToken
     */
    public function handleLinkRequest(LoginRequest $request, $userId)
    {
        $socialLogin = $this->socialLoginMapper->findByProviderUserId(
            $request->getProvider(),
            $request->getProviderUserId()
        );

        if ($socialLogin) {
            throw new LinkedAccountExistsException;
        }

        $user = $this->userService->findById($userId);

        if (! $user) {
            throw new OutOfBoundsException('Account not found', self::EXCEPTION_ACCOUNT_NOT_FOUND);
        }

        $socialLoginEntity = new SocialLoginEntity;
        $socialLoginEntity->setUserId($user->getId())
            ->setProvider($request->getProvider())
            ->setProviderUserId($request->getProviderUserId())
            ->setAccessToken($request->getAccessToken())
            ->setAccessTokenExpires($request->getAccessTokenExpires())
            ->setRefreshToken($request->getRefreshToken());

        $socialLogin = $this->socialLoginMapper->persist($socialLoginEntity);

        return $this->handleLogin($user, $request);
    }

    /**
     * Register a new user account from a social login provider
     *
     * @param  LoginRequest $request
     * @return array                  Array containing user and social login
     */
    public function registerFromSocialLogin(LoginRequest $request)
    {
        $email = $request->getEmails()[0];
        $user  = $this->userService->registerWithoutPassword(array(
            'email' => $email
        ));

        $socialLoginEntity = new SocialLoginEntity;
        $socialLoginEntity->setUserId($user->getId())
            ->setProvider($request->getProvider())
            ->setProviderUserId($request->getProviderUserId())
            ->setAccessToken($request->getAccessToken())
            ->setAccessTokenExpires($request->getAccessTokenExpires())
            ->setRefreshToken($request->getRefreshToken());

        $entity = $this->socialLoginMapper->persist($socialLoginEntity);
        return array(
            'user'         => $user,
            'social_login' => $entity
        );
    }

    /**
     * Determine if a given user has a social login linked with a given provider
     *
     * @param  string $provider
     * @param  string $user
     * @return bool
     */
    public function userHasSocialLoginWithProvider($provider, $user)
    {
        return (bool) $this->socialLoginMapper->findBy([
            'provider' => $provider,
            'user_id'  => $user->getId()
        ]);
    }

    /**
     * Create an access token given a user entity
     *
     * @param  UserEntity   $user
     * @return AccessToken
     */
    public function handleLogin(UserEntity $user)
    {
        $accessToken = new AccessToken($this->tokenStorage, $this->tokenStorage);
        $token = $accessToken->createAccessToken('', $user->getId(), null, true);

        return $token;
    }

    /**
     * @param OAuth2ZendDb $storage
     */
    public function setOAuthStorage(OAuth2ZendDb $storage)
    {
        $this->tokenStorage = $storage;
        return $this;
    }

    /**
     * @param SocialLoginMapper $mapper
     */
    public function setSocialLoginMapper(SocialLoginMapper $mapper)
    {
        $this->socialLoginMapper = $mapper;
        return $this;
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
