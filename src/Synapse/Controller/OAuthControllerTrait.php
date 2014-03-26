<?php

namespace Synapse\Controller;

use Symfony\Component\HttpFoundation\Request;

use OAuth2\Server;
use OAuth2\HttpFoundationBridge\Request as OAuthRequest;

trait OAuthControllerTrait
{
    protected $server;

    protected $authRequired = true;

    public function isAuthenticated(Request $request)
    {
        $oauthRequest = OAuthRequest::createFromRequest($request);
        return $this->server->verifyResourceRequest($oauthRequest);
    }

    public function setOAuth2Server(Server $server)
    {
        $this->server = $server;
        return $this;
    }
}
