<?php

namespace Synapse\OAuth2;

use Mustache_Engine;

use Synapse\User\UserService;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use OAuth2\HttpFoundationBridge\Response as BridgeResponse;
use OAuth2\HttpFoundationBridge\Request as OAuthRequest;
use OAuth2\Server as OAuth2Server;

use Synapse\Security\SecurityAwareInterface;
use Synapse\Security\SecurityAwareTrait;
use Synapse\Controller\AbstractController;
use Synapse\Stdlib\Arr;

use OutOfBoundsException;

class OAuthController extends AbstractController implements SecurityAwareInterface
{
    use SecurityAwareTrait;

    const AUTHORIZE_FORM_SUBMIT_ROUTE_NAME = 'oauth-authorize-form-submit';

    protected $server;
    protected $userService;
    protected $accessTokenMapper;
    protected $refreshTokenMapper;
    protected $mustache;
    protected $session;

    public function __construct(
        OAuth2Server $server,
        UserService $userService,
        AccessTokenMapper $accessTokenMapper,
        RefreshTokenMapper $refreshTokenMapper,
        Mustache_Engine $mustache,
        Session $session
    ) {
        $this->server             = $server;
        $this->userService        = $userService;
        $this->accessTokenMapper  = $accessTokenMapper;
        $this->refreshTokenMapper = $refreshTokenMapper;
        $this->mustache           = $mustache;
        $this->session            = $session;
    }

    /**
     * The user is directed here to log in
     *
     * @param Request $request
     */
    public function authorize(Request $request)
    {
        $submitUrl = $this->url(self::AUTHORIZE_FORM_SUBMIT_ROUTE_NAME);

        $vars = array();
        foreach ($request->query->all() as $param => $value) {
            $vars[] = array(
                'name'  => $param,
                'value' => $value
            );
        }

        return $this->mustache->render('OAuth/Authorize', array(
            'submitUrl' => $submitUrl,
            'vars'      => $vars,
        ));
    }

    public function authorizeFormSubmit(Request $request)
    {
        $username = $request->query->get('username');
        $user     = $this->userService->findByEmail($username);

        if (! $user) {
            return $this->createInvalidCredentialResponse();
        }

        $attemptedPassword = $request->query->get('password');
        $hashedPassword    = $user->getPassword();

        $correctPassword = password_verify($attemptedPassword, $hashedPassword);

        if (! $correctPassword) {
            return $this->createInvalidCredentialResponse();
        }

        // Automatically authorize the user
        $authorized    = true;
        $oauthRequest  = OAuthRequest::createFromRequest($request);
        $oauthResponse = new BridgeResponse();

        $response = $this->server->handleAuthorizeRequest(
            $oauthRequest,
            $oauthResponse,
            $authorized,
            $user->getId()
        );

        return $response;
    }

    public function token(Request $request)
    {
        $bridgeResponse = new BridgeResponse;
        $oauthRequest   = OAuthRequest::createFromRequest($request);

        $response = $this->server->handleTokenRequest($oauthRequest, $bridgeResponse);

        if ($response->isOk()) {
            $userId = $response->getParameter('user_id');

            $this->setLastLogin($userId);

            $this->session->set('user', $userId);
        }

        return $response;
    }

    protected function setLastLogin($userId)
    {
        $user = $this->userService->findById($userId);

        $result = $this->userService->update($user, [
            'last_login' => time()
        ]);
    }

    public function logout(Request $request)
    {
        $content = json_decode($request->getContent(), true);

        $securityToken = $this->security->getToken();
        $user          = $securityToken->getUser();
        $accessToken   = $securityToken->getOAuthToken();
        $refreshToken  = Arr::get($content, 'refresh_token');

        $this->session->set('user', null);

        if (! $refreshToken) {
            return new Response('Refresh token not provided', 422);
        }

        try {
            $this->expireAccessToken($accessToken);
            $this->expireRefreshToken($refreshToken, $user);
        } catch (OutOfBoundsException $e) {
            return new Response($e->getMessage(), 422);
        }

        return new Response('', 200);
    }

    protected function createInvalidCredentialResponse()
    {
        return $this->createSimpleResponse(422, 'Invalid credentials');
    }

    protected function expireAccessToken($accessToken)
    {
        // Expire access token
        $token = $this->accessTokenMapper->findBy([
            'access_token' => $accessToken
        ]);

        if (! $token) {
            throw new OutOfBoundsException('Access token not found.');
        }

        $token->setExpires(date("Y-m-d H:i:s", time()));

        $this->accessTokenMapper->update($token);
    }

    protected function expireRefreshToken($refreshToken, $user)
    {
        $token = $this->refreshTokenMapper->findBy([
            'refresh_token' => $refreshToken,
            'user_id'       => $user->getId(),
        ]);

        if (! $token) {
            throw new OutOfBoundsException('Refresh token not found.');
        }

        $token->setExpires(date("Y-m-d H:i:s", time()));

        $this->refreshTokenMapper->update($token);
    }
}
