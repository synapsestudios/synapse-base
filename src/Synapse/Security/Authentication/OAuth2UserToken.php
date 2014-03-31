<?php

namespace Synapse\Security\Authentication;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class OAuth2UserToken extends AbstractToken
{
    protected $token;
    public $request;

    public function __construct(array $roles = array())
    {
        parent::__construct($roles);

        $this->setAuthenticated(count($roles) > 0);
    }

    /**
     * Set the OAuth token
     *
     * @param string $token
     */
    public function setOAuthToken($token)
    {
        $this->token = $token;
    }

    /**
     * Get the OAuth token
     *
     * @return string
     */
    public function getOAuthToken()
    {
        return $this->token;
    }

    public function getCredentials()
    {
        return '';
    }
}
