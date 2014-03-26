<?php

namespace Synapse\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Symfony\Component\Console\Application as ConsoleApplication;

use Synapse\Config\Config;
use Synapse\Config\FileReader;

/**
 * Service provider for migration and upgrade console commands
 */
class MigrationUpgradeServiceProvider implements ServiceProviderInterface
{
    /**
     * Register console commands as services
     *
     * @param  Silex\Application $app
     */
    public function register(Application $app)
    {
        $initConfig = $app['config']->load('init');

        $app['upgrade.create'] = $app->share(function () use ($app) {
            $command = new \Synapse\Command\Upgrade\Create(
                new \Synapse\View\Upgrade\Create($app['mustache'])
            );

            $command->setDatabaseAdapter($app['db']);

            return $command;
        });

        $app['upgrade.run'] = $app->share(function () use ($app, $initConfig) {
            $command = new \Synapse\Command\Upgrade\Run;

            $command->setDatabaseAdapter($app['db']);
            $command->setAppVersion($app['version']);
            $command->setUpgradeNamespace($initConfig['upgrades']);
            $command->setRunMigrationsCommand($app['migrations.run']);
            $command->setGenerateInstallCommand($app['install.generate']);

            return $command;
        });

        $app['install.generate'] = $app->share(function () use ($app, $initConfig) {
            $command = new \Synapse\Command\Install\Generate;

            $command->setDbConfig($app['config']->load('db'));
            $command->setInstallConfig($app['config']->load('install'));
            $command->setUpgradeNamespace($initConfig['upgrades']);

            return $command;
        });

        $app['migrations.create'] = $app->share(function () use ($app) {
            return new \Synapse\Command\Migrations\Create(
                new \Synapse\View\Migration\Create($app['mustache'])
            );
        });

        $app['migrations.run'] = $app->share(function () use ($app, $initConfig) {
            $command = new \Synapse\Command\Migrations\Run;

            $command->setDatabaseAdapter($app['db']);
            $command->setMigrationNamespace($initConfig['migrations']);

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
        $app->command('migrations.run');
        $app->command('migrations.create');
    }
}
