<?php

namespace Synapse\Provider;

use Synapse\Resque\Resque as ResqueService;
use Silex\Application;
use Silex\ServiceProviderInterface;

use Synapse\Command\Resque as ResqueCommand;

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
        $app['resque'] = $app->share(function () use ($app) {
            return new ResqueService($app['config']->load('resque'));
        });

        $app['resque.command'] = $app->share(function () use ($app) {
            $command = new ResqueCommand;
            $command->setResque($app['resque']);
            return $command;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
        $app->command('resque.command');
    }
}
