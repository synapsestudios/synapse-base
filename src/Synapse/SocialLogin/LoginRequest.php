<?php

namespace Synapse\SocialLogin;

/**
 * Value-object representing a social login request
 */
class LoginRequest
{
    protected $provider;
    protected $providerUserId;
    protected $emails = array();
    protected $accessToken;
    protected $accessTokenExpires;
    protected $refreshToken;

    /**
     * @param string       $provider
     * @param string       $providerUserId
     * @param string       $accessToken
     * @param integer      $accessTokenExpires
     * @param string|null  $refreshToken
     * @param array        $emails
     */
    public function __construct(
        $provider,
        $providerUserId,
        $accessToken,
        $accessTokenExpires = 0,
        $refreshToken = null,
        array $emails = array()
    ) {
        $this->provider           = $provider;
        $this->providerUserId     = $providerUserId;
        $this->accessToken        = $accessToken;
        $this->accessTokenExpires = $accessTokenExpires;
        $this->refreshToken       = $refreshToken;
        $this->emails             = $emails;
    }

    /**
     * Gets the value of provider.
     *
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Gets the value of providerUserId.
     *
     * @return string
     */
    public function getProviderUserId()
    {
        return $this->providerUserId;
    }

    /**
     * Gets the value of emails.
     *
     * @return array
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * Gets the value of accessToken.
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Gets the value of accessTokenExpires.
     *
     * @return integer
     */
    public function getAccessTokenExpires()
    {
        return $this->accessTokenExpires;
    }

    /**
     * Gets the value of refreshToken.
     *
     * @return string|null
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }
}
