<?php

namespace Synapse\Command;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Symfony\Component\Console\Application as ConsoleApplication;

use Synapse\Config\Config;
use Synapse\Config\FileReader;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Application $app)
    {
        $app['console'] = $app->share(function () use ($app) {
            return new ConsoleApplication;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
        // noop
    }
}
