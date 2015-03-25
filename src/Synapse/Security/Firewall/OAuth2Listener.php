<?php

namespace Synapse\Security\Firewall;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Synapse\Security\Authentication\OAuth2UserToken;

class OAuth2Listener implements ListenerInterface
{
    protected $securityContext;
    protected $authenticationManager;

    public function __construct(
        SecurityContextInterface $securityContext,
        AuthenticationManagerInterface $authenticationManager
    ) {
        $this->securityContext       = $securityContext;
        $this->authenticationManager = $authenticationManager;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($request->getMethod() === 'OPTIONS') {
            $this->securityContext->setToken(new AnonymousToken('', 'anon.', array()));
            return;
        }

        $regex = '/Bearer (.*)/';
        if (! $request->headers->has('Authorization') ||
            preg_match($regex, $request->headers->get('Authorization'), $matches) !== 1
        ) {
            $event->setResponse($this->getInvalidRequestResponse());
            return;
        }

        $token = new OAuth2UserToken();
        $token->setOAuthToken($matches[1]);
        $token->request = $request;

        try {
            $authToken = $this->authenticationManager->authenticate($token);
            $this->securityContext->setToken($authToken);

            return;
        } catch (AuthenticationException $failed) {
            $event->setResponse($this->getInvalidTokenReponse());
            return;
        }

        $event->setResponse($this->getInvalidTokenReponse());
    }

    /**
     * Return an invalid_token response object
     *
     * @return JsonResponse
     */
    protected function getInvalidTokenReponse()
    {
        return $this->getUnauthorizedResponse(401, 'invalid_token');
    }

    /**
     * Return an invalid_request response object
     *
     * @return JsonResponse
     */
    protected function getInvalidRequestResponse()
    {
        return $this->getUnauthorizedResponse(400, 'invalid_request');
    }

    /**
     * Return an "Unauthorized" request including a WWW-Authenticate header per
     * the OAuth2 specification
     *
     * @param  integer $statusCode HTTP status code to return in response
     * @param  string  $error      Error message in WWW-Authenticate header
     * @return JsonResponse
     * @link   https://tools.ietf.org/html/rfc6750#section-3
     */
    protected function getUnauthorizedResponse($statusCode, $error = null)
    {
        $authenticateHeader  = 'Bearer';
        $authenticateHeader .= $error === null ? '' : sprintf(' error="%s"', $error);

        $body    = ['message' => 'Unauthorized'];
        $headers = ['WWW-Authenticate' => $authenticateHeader];

        return new JsonResponse($body, $statusCode, $headers);
    }
}
