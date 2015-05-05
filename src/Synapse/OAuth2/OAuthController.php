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
use OAuth2\Storage\UserCredentialsInterface;

use Synapse\Security\SecurityAwareInterface;
use Synapse\Security\SecurityAwareTrait;
use Synapse\Controller\AbstractController;
use Synapse\Stdlib\Arr;

use OutOfBoundsException;

/**
 * Controller implementing OAuth2
 *
 * Implements "Authorization Code" grant type:
 *     OAuthController::authorize
 *     OAuthController::authorizeFormSubmit
 *
 * Implements "Resource Owner Password Credentials" grant type:
 *     OAuthController::token
 */
class OAuthController extends AbstractController implements SecurityAwareInterface
{
    use SecurityAwareTrait;

    /**
     * Name of the route to which login form submissions should post
     *
     * @var string
     */
    const AUTHORIZE_FORM_SUBMIT_ROUTE_NAME = 'oauth-authorize-form-submit';

    /**
     * Name of the template for the login form
     *
     * @var string
     */
    const AUTHORIZE_FORM_TEMPLATE = 'OAuth/Authorize';

    /**
     * @var OAuth2Server
     */

    protected $server;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var AccessTokenMapper
     */
    protected $accessTokenMapper;

    /**
     * @var RefreshTokenMapper
     */
    protected $refreshTokenMapper;

    /**
     * @var Mustache_Engine
     */
    protected $mustache;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var boolean
     */
    protected $requireVerification;

    /**
     * @param OAuth2Server       $server
     * @param UserService        $userService
     * @param AccessTokenMapper  $accessTokenMapper
     * @param RefreshTokenMapper $refreshTokenMapper
     * @param Mustache_Engine    $mustache
     * @param Session            $session
     * @param boolean            $requireVerification
     */
    public function __construct(
        OAuth2Server $server,
        UserService $userService,
        AccessTokenMapper $accessTokenMapper,
        RefreshTokenMapper $refreshTokenMapper,
        Mustache_Engine $mustache,
        Session $session,
        $requireVerification
    ) {
        $this->server              = $server;
        $this->userService         = $userService;
        $this->accessTokenMapper   = $accessTokenMapper;
        $this->refreshTokenMapper  = $refreshTokenMapper;
        $this->mustache            = $mustache;
        $this->session             = $session;
        $this->requireVerification = $requireVerification;
    }

    /**
     * The user is directed here to log in (Part 1 of the "Authorization Code" grant type)
     *
     * @link http://tools.ietf.org/html/rfc6749#section-4.1.1 Authorization Request
     * @param Request $request
     * @return Response
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

        return $this->mustache->render(self::AUTHORIZE_FORM_TEMPLATE, array(
            'submitUrl' => $submitUrl,
            'vars'      => $vars,
        ));
    }

    /**
     * Handle submission from login form (Part 2 of the "Authorization Code" grant type)
     *
     * @link http://tools.ietf.org/html/rfc6749#section-4.1.1 Authorization Request
     * @param  Request $request
     * @return Response
     */
    public function authorizeFormSubmit(Request $request)
    {
        $user = $this->getUserFromRequest($request);

        if (! $user) {
            return $this->createInvalidCredentialResponse();
        }

        // If enabled in config, check that user is verified
        if ($this->requireVerification && $user->getVerified() !== '1') {
            return $this->createInvalidCredentialResponse();
        }

        if ($user->getEnabled() !== '1') {
            return $this->createInvalidCredentialResponse();
        }

        $attemptedPassword = $request->get('password');
        $hashedPassword    = $user->getPassword();

        $correctPassword = $this->verifyPassword($attemptedPassword, $hashedPassword);

        if (! $correctPassword) {
            return $this->createInvalidCredentialResponse();
        }

        // Automatically authorize the user
        $authorized = true;

        // The OAuth2 library assumes variables as GET params, but for security purposes they are POST. Convert here.
        $requestData = ($request->getMethod() === 'GET') ? $request->query : $request->request;

        $oauthRequest  = new OAuthRequest($requestData->all());
        $oauthResponse = new BridgeResponse();

        $response = $this->server->handleAuthorizeRequest(
            $oauthRequest,
            $oauthResponse,
            $authorized,
            $user->getId()
        );

        return $response;
    }

    /**
     * Handle an OAuth token request
     *
     * (Implements the "Resource Owner Password Credentials" grant type
     * or Part 3 of the "Authorization Code" grant type)
     *
     * Note: Expects input as POST variables, not JSON request body
     *
     * @link http://tools.ietf.org/html/rfc6749#section-4.3.2 Access Token Request
     * @param  Request $request
     * @return Response
     */
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

    /**
     * Set the last_login timestamp in the database
     *
     * @param string $userId
     */
    protected function setLastLogin($userId)
    {
        $user = $this->userService->findById($userId);

        $result = $this->userService->update($user, [
            'last_login' => time()
        ]);
    }

    /**
     * Handle logout request
     *
     * @param  Request $request
     * @return Response
     */
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

    /**
     * Create a unified response for invalid login credentials
     *
     * @return Response
     */
    protected function createInvalidCredentialResponse()
    {
        return $this->createSimpleResponse(422, 'Invalid credentials');
    }

    /**
     * Expire an access token
     *
     * @param  string $accessToken
     */
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

    /**
     * Expire a refresh token
     *
     * @param  string     $refreshToken
     * @param  UserEntity $user
     */
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

    /**
     * Verify that the password is correct
     *
     * @param  string $attemptedPassword Password being verified
     * @param  string $hashedPassword    Correct hashed password
     * @return boolean
     */
    protected function verifyPassword($attemptedPassword, $hashedPassword)
    {
        return password_verify($attemptedPassword, $hashedPassword);
    }

    /**
     * Get the user from the request
     *
     * @param  Request $request
     * @return UserEntity|boolean
     */
    protected function getUserFromRequest(Request $request)
    {
        $username = $request->get('username');

        return $this->userService->findByEmail($username);
    }
}
