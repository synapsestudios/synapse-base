<?php

namespace Synapse\SocialLogin;

use OAuth\ServiceFactory;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\RequestMatcher;

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
                ->setServiceFactory(new ServiceFactory())
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

        $this->setFirewalls($app);
    }

    /**
     * Perform extra chores on boot (none needed here)
     *
     * @param  Application $app
     */
    public function boot(Application $app)
    {
        // Noop
    }

    /**
     * Set social login related firewalls
     *
     * @param Application $app
     */
    protected function setFirewalls(Application $app)
    {
        $app->extend('security.firewalls', function ($firewalls, $app) {
            $socialLoginLink = new RequestMatcher('^/social-login/[a-z]+/link', null, ['GET']);
            $socialLogin     = new RequestMatcher('^/social-login', null, ['GET']);

            $socialFirewalls = [
                'social-login-link' => [
                    'pattern' => $socialLoginLink,
                    'oauth'   => true,
                ],
                'social-login' => [
                    'pattern'   => $socialLogin,
                    'anonymous' => true,
                ],
            ];

            return array_merge($socialFirewalls, $firewalls);
        });
    }
}
