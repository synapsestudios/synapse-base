<?php

namespace Synapse\Migration;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Service provider for migration console commands
 */
class MigrationServiceProvider implements ServiceProviderInterface
{
    /**
     * Register console commands as services
     *
     * @param  Silex\Application $app
     */
    public function register(Application $app)
    {
        $app['migrations.create'] = $app->share(function () use ($app) {
            $command = new \Synapse\Command\Migrations\Create(
                new \Synapse\View\Migration\Create($app['mustache'])
            );

            $config = $app['config']->load('init');

            if (isset($config['migrations_namespace'])) {
                $command->setMigrationNamespace($config['migrations_namespace']);
            }

            return $command;
        });

        $app['migrations.run'] = $app->share(function () use ($app) {
            $command = new \Synapse\Command\Migrations\Run;

            $command->setDatabaseAdapter($app['db']);

            $config = $app['config']->load('init');

            if (isset($config['migrations_namespace'])) {
                $command->setMigrationNamespace($config['migrations_namespace']);
            }

            return $command;
        });
    }

    /**
     * Perform chores on boot. (None required here.)
     *
     * @param  Application $app
     */
    public function boot(Application $app)
    {
        // Register command routes
        $app->command('migrations.run');
        $app->command('migrations.create');
    }
}
