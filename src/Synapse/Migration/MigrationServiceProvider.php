<?php

namespace Synapse\Migration;

use Synapse\View\Migration\Create as CreateMigrationView;
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
        $app['migrations.create'] = $app->share(function ($app) {
            $view = new CreateMigrationView($app['mustache']);

            $command = new CreateMigrationCommand('migrations:create', $view);

            $config = $app['config']->load('init');

            if (isset($config['migrations_namespace'])) {
                $command->setMigrationNamespace($config['migrations_namespace']);
            }

            return $command;
        });

        $app['migrations.run'] = $app->share(function ($app) {
            $command = new RunMigrationsCommand('migrations:run');

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
