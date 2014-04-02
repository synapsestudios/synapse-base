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
        // Register log provider first to catch any exceptions thrown in the others
        $app->register(new \Synapse\Log\ServiceProvider);

        $app->register(new \Synapse\Command\ServiceProvider);
        $app->register(new \Synapse\Db\ServiceProvider;
        $app->register(new \Synapse\OAuth2\ServerServiceProvider);
        $app->register(new \Synapse\OAuth2\SecurityServiceProvider);
        $app->register(new \Synapse\Resque\ServiceProvider);
        $app->register(new \Synapse\Controller\ServiceProvider);
        $app->register(new \Synapse\Email\ServiceProvider);
        $app->register(new \Synapse\User\ServiceProvider);
        $app->register(new \Synapse\Migration\ServiceProvider);
        $app->register(new \Synapse\Upgrade\ServiceProvider);
        $app->register(new \Synapse\Session\ServiceProvider);
        $app->register(new \Synapse\SocialLogin\ServiceProvider);

        $app->register(new \Synapse\View\ServiceProvider, [
            'mustache.paths' => array(
                APPDIR.'/templates'
            ),
            'mustache.options' => [
                'cache' => TMPDIR,
            ],
        ]);

        $app->register(new \Silex\Provider\UrlGeneratorServiceProvider);

        // Register the CORS middleware
        $app->register(new \JDesrosiers\Silex\Provider\CorsServiceProvider);
        $app->after($app['cors']);
    }

    /**
     * Register the security context
     *
     * @param  Application $app
     */
    public function registerSecurity(Application $app)
    {
        $app->register(new \Silex\Provider\SecurityServiceProvider, [
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
