<?php

namespace Synapse\Security\Authentication;

use OAuth2\Server as OAuth2Server;
use OAuth2\HttpFoundationBridge\Request as OAuthRequest;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Synapse\Security\Authentication\OAuth2UserToken;
use Synapse\User\RoleService;

class OAuth2Provider implements AuthenticationProviderInterface
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var RoleService
     */
    private $roleService;

    /**
     * @var OAuth2Server
     */
    private $server;

    /**
     * @param UserProviderInterface $userProvider
     * @param RoleService           $roleService
     * @param OAuth2Server          $server
     */
    public function __construct(
        UserProviderInterface $userProvider,
        RoleService $roleService,
        OAuth2Server $server
    ) {
        $this->userProvider = $userProvider;
        $this->roleService  = $roleService;
        $this->server       = $server;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(TokenInterface $token)
    {
        $oauthRequest = OAuthRequest::createFromRequest($token->request);

        // Not authenticated
        if (!$this->server->verifyResourceRequest($oauthRequest)) {
            throw new AuthenticationException('OAuth2 authentication failed');
        }

        $userData = $this->server->getAccessTokenData($oauthRequest);

        $user  = $this->userProvider->findById($userData['user_id']);
        $roles = $this->roleService->findRoleNamesByUserId($user->getId());

        $user->setRoles($roles);

        $authenticatedToken = new OAuth2UserToken($roles);
        $authenticatedToken->setUser($user);
        $authenticatedToken->setAuthenticated(true);
        $authenticatedToken->setOAuthToken($token->getOAuthToken());

        return $authenticatedToken;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof OAuth2UserToken;
    }
}
