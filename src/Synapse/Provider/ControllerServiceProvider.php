<?php

namespace Synapse\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ControllerServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the controller resolver and initializers
     *
     * @param  Application $app
     */
    public function register(Application $app)
    {
        $app['resolver'] = $app->share($app->extend('resolver', function ($resolver, $app) {
            return new ControllerResolver($resolver, $app);
        }));

        $app->initializer(
            'Synapse\\Application\\UrlGeneratorAwareInterface',
            function ($object) use ($app) {
                $object->setUrlGenerator($app['url_generator']);
                return $object;
            }
        );

        $app->initializer(
            'Synapse\\Application\\SecurityAwareInterface',
            function ($object) use ($app) {
                $object->setSecurityContext($app['security']);
                return $object;
            }
        );
    }

    /**
     * Perform chores on boot. (None required here.)
     *
     * @param  Application $app
     */
    public function boot(Application $app)
    {
        // noop
    }
}
