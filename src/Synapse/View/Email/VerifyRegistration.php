<?php

namespace Synapse\View\Email;

use Synapse\View\AbstractView;
use Synapse\User\Entity\User;
use Synapse\User\Entity\UserToken;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * View for email sent to users to verify their registration
 */
class VerifyRegistration extends AbstractView
{
    /**
     * @var Synapse\User\Entity\UserToken
     */
    protected $userToken;

    /**
     * @var Symfony\Component\Routing\Generator\UrlGenerator
     */
    protected $urlGenerator;

    /**
     * @param UserToken $userToken
     */
    public function setUserToken(UserToken $userToken)
    {
        $this->userToken = $userToken;
    }

    /**
     * @param UrlGenerator $urlGenerator
     */
    public function setUrlGenerator(UrlGenerator $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @todo Change this URL to a route in the javascript app that will perform
     *       the API request since the API expects the token as a POST parameter
     *
     * @return string
     */
    public function url()
    {
        $parameters = [
            'id'    => $this->userToken->getUserId(),
            'token' => $this->userToken->getToken(),
        ];

        $url = $this->urlGenerator->generate(
            'verify-registration',
            $parameters,
            UrlGenerator::ABSOLUTE_URL
        );

        return $url;
    }
}
