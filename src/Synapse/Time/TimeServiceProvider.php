<?php

namespace Synapse\Time;

use Silex\ServiceProviderInterface;
use Silex\Application;

class TimeServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Application $app)
    {
        $app->initializer(
            'Synapse\Time\TimeAwareInterface',
            function ($object, $app) {
                $object->setTimeObject(new Time());
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
