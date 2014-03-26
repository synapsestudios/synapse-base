<?php

/*
 * Custom service provider for mustache
 */

namespace Synapse\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class MustacheServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['mustache.options'] = array();

        $app['mustache'] = $app->share(function ($app) {
            $defaults = array(
                'loader'          => $app['mustache.loader'],
                'partials_loader' => $app['mustache.partials_loader'],
                'helpers'         => $app['mustache.helpers'],
                'charset'         => $app['charset'],
            );

            if (isset($app['logger'])) {
                $defaults['logger'] = $app['logger'];
            }

            $app['mustache.options'] = array_replace($defaults, $app['mustache.options']);

            return new \Mustache_Engine($app['mustache.options']);
        });

        $app['mustache.loader'] = $app->share(function ($app) {
            if (! isset($app['mustache.paths'])) {
                return new \Mustache_Loader_StringLoader;
            }

            if (! is_array($app['mustache.paths'])) {
                return new \Mustache_Loader_FilesystemLoader($app['mustache.paths']);
            }

            $loader = new \Mustache_Loader_CascadingLoader;
            foreach ($app['mustache.paths'] as $path) {
                $pathLoader = new \Mustache_Loader_FilesystemLoader($path);
                $loader->addLoader($pathLoader);
            }

            return $loader;
        });

        $app['mustache.partials_loader'] = $app->share(function ($app) {
            if (isset($app['mustache.partials_path'])) {
                return new \Mustache_Loader_FilesystemLoader($app['mustache.partials_path']);
            } elseif (isset($app['mustache.partials'])) {
                return new \Mustache_Loader_ArrayLoader($app['mustache.partials']);
            } else {
                return $app['mustache.loader'];
            }
        });

        $app['mustache.helpers'] = array();
    }

    public function boot(Application $app)
    {
        // nada
    }
}
