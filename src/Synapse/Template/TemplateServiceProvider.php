<?php
namespace Synapse\Template;

use Handlebars\Handlebars;
use Handlebars\Loader\FilesystemLoader;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Synapse\Stdlib\Arr;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class TemplateServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['css-to-inline'] = $app->share(function ($app) {
            return new CssToInlineStyles();
        });

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

        $app['template.service'] = $app->share(function ($app) {
            return new TemplateService(
                $app['handlebars'],
                $app['css-to-inline']
            );
        });
    }

    public function boot(Application $app)
    {
    }
}
