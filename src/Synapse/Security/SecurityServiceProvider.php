<?php

namespace Synapse\Security;

use Silex\ServiceProviderInterface;
use Silex\Application;

class SecurityServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Application $app)
    {
        $app->initializer(
            'Synapse\Security\SecurityAwareInterface',
            function ($object) use ($app) {
                $object->setSecurityContext($app['security']);
                return $object;
            }
        );
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
        // Noop
    }
}
