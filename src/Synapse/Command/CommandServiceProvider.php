<?php

namespace Synapse\Command;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Symfony\Component\Console\Application as ConsoleApplication;

use Synapse\Config\Config;
use Synapse\Config\FileReader;

class CommandServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Application $app)
    {
        $app['console'] = $app->share(function ($app) {
            $consoleApp = new ConsoleApplication;
            // disable swallowing exceptions so they actually get pushed to the logger
            $consoleApp->setCatchExceptions(false);
            return $consoleApp;
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
