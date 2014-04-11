<?php

namespace Synapse\Session;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class SessionServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Application $app)
    {
        $app['session'] = $app->share(function ($app) {
            return new Session(new NativeSessionStorage());
        });
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
        $app['session']->start();
    }
}
