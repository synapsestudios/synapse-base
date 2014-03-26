<?php

namespace Synapse\Application;

use Symfony\Component\Security\Core\SecurityContext;

/**
 * Trait for inserting the security context as a property and returning the current user
 */
trait SecurityAwareTrait
{
    /**
     * @var SecurityContext
     */
    protected $security;

    /**
     * @param SecurityContext $security
     */
    public function setSecurityContext(SecurityContext $security)
    {
        $this->security = $security;
        return $this;
    }

    /**
     * Gets a user from the Security Context.
     * Borrowed from Silex\Application\SecurityTrait
     *
     * @return mixed
     *
     * @see TokenInterface::getUser()
     */
    public function user()
    {
        if (null === $token = $this->security->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }
}
