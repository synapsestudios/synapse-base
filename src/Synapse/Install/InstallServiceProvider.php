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
        $app['install.generate-proxy'] = $app->share(function ($app) {
            $command = new GenerateInstallCommandProxy('install:generate');
            $command->setFactory($app->raw('install.generate'))
                ->setApplication($app);
            return $command;
        });

        $app['install.generate'] = $app->share(function ($app) {
            $command = new GenerateInstallCommand();

            $command->setDbConfig($app['config']->load('db'));
            $command->setInstallConfig($app['config']->load('install'));

            return $command;
        });

        $app['install.run-proxy'] = $app->share(function ($app) {
            $command = new RunInstallCommandProxy('install:run');
            $command->setFactory($app->raw('install.run'))
                ->setApplication($app);
            return $command;
        });

        $app['install.run'] = $app->share(function ($app) {
            $command = new RunInstallCommand();

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
        $app->command('install.run-proxy');
        $app->command('install.generate');
    }
}
