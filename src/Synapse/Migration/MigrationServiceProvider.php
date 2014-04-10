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
            return new \Synapse\Command\Migrations\Create(
                new \Synapse\View\Migration\Create($app['mustache'])
            );
        });

        $app['migrations.run'] = $app->share(function () use ($app) {
            $command = new \Synapse\Command\Migrations\Run;

            $command->setDatabaseAdapter($app['db']);

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
