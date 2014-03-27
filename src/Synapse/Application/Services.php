<?php

namespace Synapse\Application;

use Synapse\Application;

/**
 * Define services
 */
class Services implements ServicesInterface
{
    /**
     * {@inheritDoc}
     * @param  Application $app
     */
    public function register(Application $app)
    {
        $this->registerServiceProviders($app);
        $this->registerSecurity($app);
    }

    /**
     * Register various service providers
     *
     * @param  Application $app
     */
    protected function registerServiceProviders(Application $app)
    {
        // Load log provider first to catch any exceptions thrown in the others
        $app->register(new \Synapse\Log\ServiceProvider());

        $app->register(new \Synapse\Provider\ConsoleServiceProvider());
        $app->register(new \Synapse\Provider\ZendDbServiceProvider());
        $app->register(new \Synapse\Provider\OAuth2ServerServiceProvider());
        $app->register(new \Synapse\Provider\OAuth2SecurityServiceProvider());
        $app->register(new \Synapse\Provider\ResqueServiceProvider());
        $app->register(new \Synapse\Provider\ControllerServiceProvider());
        $app->register(new \Synapse\Email\ServiceProvider());
        $app->register(new \Synapse\User\ServiceProvider());

        // Register the CORS middleware
        $app->register(new \JDesrosiers\Silex\Provider\CorsServiceProvider());
        $app->after($app['cors']);

        $app->register(new \Synapse\Session\ServiceProvider());

        $app->register(new \Silex\Provider\UrlGeneratorServiceProvider());
        $app->register(new \Synapse\SocialLogin\ServiceProvider());

        $app->register(new \Synapse\Provider\MustacheServiceProvider, [
            'mustache.paths' => array(
                APPDIR.'/templates'
            ),
            'mustache.options' => [
                'cache' => TMPDIR,
            ],
        ]);

        $app->register(new \Synapse\Provider\MigrationUpgradeServiceProvider());
    }

    /**
     * Register the security context
     *
     * @param  Application $app
     */
    public function registerSecurity(Application $app)
    {
        $app->register(new \Silex\Provider\SecurityServiceProvider(), [
            'security.firewalls' => [
                'unsecured' => [
                    'pattern'   => '^/(oauth|social-login)',
                ],
                'public' => [
                    'pattern'   => '^/users$', // Make user creation endpoint public for user registration
                    'anonymous' => true,
                ],
                'api' => [
                    'pattern'   => '^/',
                    // Order of oauth and anonymous matters!
                    'oauth'     => true,
                    'anonymous' => true,
                ],
            ],
        ]);
    }
}
