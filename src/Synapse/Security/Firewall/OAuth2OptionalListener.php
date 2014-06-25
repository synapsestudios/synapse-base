<?php

namespace Synapse\Security\Firewall;

/**
 * Listener which sets the security token on the security context if the user is logged in,
 * but does not forbid access if the user is not logged in.
 */
class OAuth2OptionalListener extends OAuth2Listener
{
    /**
     * {@inheritDoc}
     */
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
}
