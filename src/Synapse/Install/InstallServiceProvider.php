<?php

namespace Synapse\Install;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Service provider for install console commands
 */
class InstallServiceProvider implements ServiceProviderInterface
{
    /**
     * Register console commands as services
     *
     * @param  Silex\Application $app
     */
    public function register(Application $app)
    {
        $app['install.generate'] = $app->share(function ($app) {
            $command = new GenerateInstallCommand('install:generate');

            $command->setDbConfig($app['config']->load('db'));
            $command->setInstallConfig($app['config']->load('install'));

            return $command;
        });

        $app['install.run'] = $app->share(function ($app) {
            $command = new RunInstallCommand('install:run');

            $command->setDatabaseAdapter($app['db']);
            $command->setAppVersion($app['version']);
            $command->setRunMigrationsCommand($app['migrations.run']);
            $command->setGenerateInstallCommand($app['install.generate']);

            return $command;
        });
    }

    /**
     * Perform chores on boot.
     *
     * @param  Application $app
     */
    public function boot(Application $app)
    {
        // Register command routes
        $app->command('install.run');
        $app->command('install.generate');
    }
}
