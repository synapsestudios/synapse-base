<?php

namespace Synapse\SocialLogin;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Service provider for logging services.
 *
 * Register application logger and injected log handlers.
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * Register social login services
     *
     * @param  Application $app Silex application
     */
    public function register(Application $app)
    {
        $app['social-login.controller'] = $app->share(function () use ($app) {
            $config = $app['config']->load('social-login');

            $controller = new Controller\SocialLoginController;
            $controller
                ->setSocialLoginService($app['social-login.service'])
                ->setConfig($config)
                ->setSession($app['session']);

            return $controller;
        });

        $app['social-login.mapper'] = $app->share(function () use ($app) {
            return new SocialLoginMapper($app['db'], new SocialLoginEntity);
        });

        $app['social-login.service'] = $app->share(function () use ($app) {
            $service = new SocialLoginService;
            $service->setUserService($app['user.service'])
                ->setSocialLoginMapper($app['social-login.mapper'])
                ->setOAuthStorage($app['oauth.storage']);

            return $service;
        });

        $app->get('/social-login/{provider}', 'social-login.controller:login')
            ->bind('social-login-auth');

        $app->get('/social-login/{provider}/link', 'social-login.controller:link')
            ->bind('social-link-auth');

        $app->get('/social-login/{provider}/callback', 'social-login.controller:callback')
            ->bind('social-login-callback');
    }

    /**
     * Perform extra chores on boot (none needed here)
     *
     * @param  Application $app
     */
    public function boot(Application $app)
    {
        // noop
    }
}
