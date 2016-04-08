<?php
namespace Synapse\Template;

use Handlebars\Handlebars;
use Handlebars\Loader\FilesystemLoader;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Synapse\Stdlib\Arr;

class TemplateServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['handlebars'] = $app->share(function ($app) {
            $emailConfig = $app['config']->load('template');

            $templatePath = Arr::path($emailConfig, 'handlebars.path', APPDIR . '/templates');

            return new Handlebars([
                'loader' => new FilesystemLoader(
                    $templatePath,
                    [
                        'extension' => '.hbs',
                    ]
                ),
                'partials_loader' => new FilesystemLoader(
                    $templatePath,
                    [
                        'prefix' => '_',
                    ]
                )
            ]);
        });
    }

    public function boot(Application $app)
    {
    }
}
