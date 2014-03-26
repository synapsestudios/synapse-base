<?php

namespace Synapse\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Symfony\Component\Console\Application as ConsoleApplication;

use Synapse\Config\Config;
use Synapse\Config\FileReader;

class ConsoleServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['console'] = $app->share(function () use ($app) {
            return new ConsoleApplication;
        });
    }

    public function boot(Application $app)
    {
        // noop
    }
}
