<?php

namespace Synapse\Security\Firewall;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
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

    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($request->getMethod() === 'OPTIONS') {
            return;
        }

        $regex = '/Bearer (.*)/';
        if (! $request->headers->has('Authorization') ||
            preg_match($regex, $request->headers->get('Authorization'), $matches) !== 1
        ) {
            $event->setResponse($this->getForbiddenReponse());
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
            $event->setResponse($this->getForbiddenReponse());
            return;
        }

        $event->setResponse($this->getForbiddenReponse());
    }

    protected function getForbiddenReponse()
    {
        $response = new Response();
        $response->setStatusCode(401);
        return $response;
    }
}
