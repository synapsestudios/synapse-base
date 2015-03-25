<?php

namespace Synapse\Security;

use Symfony\Component\Security\Core\SecurityContext;

interface SecurityAwareInterface
{
    /**
     * @param SecurityContext $security
     */
    public function setSecurityContext(SecurityContext $security);

    /**
     * Get a user from the Security Context
     *
     * @return mixed
     */
    public function getUser();
}
