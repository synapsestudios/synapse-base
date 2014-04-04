<?php

namespace Synapse\Upgrade;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Service provider for upgrade console commands
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * Register console commands as services
     *
     * @param  Silex\Application $app
     */
    public function register(Application $app)
    {
        $app['upgrade.create'] = $app->share(function () use ($app) {
            $command = new \Synapse\Command\Upgrade\Create(
                new \Synapse\View\Upgrade\Create($app['mustache'])
            );

            $command->setDatabaseAdapter($app['db']);

            return $command;
        });

        $app['upgrade.run'] = $app->share(function () use ($app) {
            $command = new \Synapse\Command\Upgrade\Run;

            $command->setDatabaseAdapter($app['db']);
            $command->setAppVersion($app['version']);
            $command->setRunMigrationsCommand($app['migrations.run']);
            $command->setGenerateInstallCommand($app['install.generate']);

            return $command;
        });

        $app['install.generate'] = $app->share(function () use ($app) {
            $command = new \Synapse\Command\Install\Generate;

            $command->setDbConfig($app['config']->load('db'));
            $command->setInstallConfig($app['config']->load('install'));

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
        $app->command('upgrade.run');
        $app->command('upgrade.create');
        $app->command('install.generate');
    }
}
