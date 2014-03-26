<?php

namespace Synapse\Session;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Drak\NativeSession\NativeRedisSessionHandler;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['session.storage.handler'] = $app->share(function () {
            return new \SessionHandler('123');
        });
    }

    public function boot(Application $app)
    {
        $app['session']->start();
    }
}
