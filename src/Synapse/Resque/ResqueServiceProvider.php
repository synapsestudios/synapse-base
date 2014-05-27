<?php

namespace Synapse\Resque;

use Synapse\Resque\Resque as ResqueService;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Service provider for Resque services
 */
class ResqueServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Application $app)
    {
        $app['resque'] = $app->share(function ($app) {
            return new ResqueService($app['config']->load('resque'));
        });

        $app['resque.command'] = $app->share(function ($app) {
            $command = new ResqueCommand('resque');
            $command->setResque($app['resque']);
            return $command;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
        // Register command routes
        $app->command('resque.command');
    }
}
