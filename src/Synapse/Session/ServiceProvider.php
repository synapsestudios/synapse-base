<?php

namespace Synapse\Session;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['session'] = $app->share(function ($app) {
            return new Session(new NativeSessionStorage());
        });
    }

    public function boot(Application $app)
    {
        $app['session']->start();
    }
}
