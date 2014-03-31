<?php

namespace Synapse\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Synapse\Config\Config;
use Synapse\Config\FileReader;

class ConfigServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Application $app)
    {
        $app['config'] = $app->share(function () use ($app) {
            $config = new Config();

            foreach ($app['config_dirs'] as $directory) {
                $config->attach(new FileReader($directory));
            }

            return $config;
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
