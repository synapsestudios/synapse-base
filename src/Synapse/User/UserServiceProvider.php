<?php

namespace Synapse\User;

use Synapse\User\Token\TokenEntity;
use Synapse\User\Token\TokenMapper;
use Synapse\View\Email\VerifyRegistration as VerifyRegistrationView;
use Synapse\View\Email\ResetPassword as ResetPasswordView;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\RequestMatcher;

/**
 * Service provider for user related services
 */
class UserServiceProvider implements ServiceProviderInterface
{
    /**
     * Register services related to Users
     *
     * @param  Application $app
     */
    public function register(Application $app)
    {
        $app['user.mapper'] = $app->share(function () use ($app) {
            return new UserMapper($app['db'], new UserEntity);
        });

        $app['user-token.mapper'] = $app->share(function () use ($app) {
            return new TokenMapper($app['db'], new TokenEntity);
        });

        $app['user.service'] = $app->share(function () use ($app) {
            $verifyRegistrationView = new VerifyRegistrationView($app['mustache']);
            $verifyRegistrationView->setUrlGenerator($app['url_generator']);

            $resetPasswordView = new ResetPasswordView($app['mustache']);
            $resetPasswordView->setUrlGenerator($app['url_generator']);

            $service = new UserService;
            $service->setUserMapper($app['user.mapper'])
                ->setTokenMapper($app['user-token.mapper'])
                ->setEmailService($app['email.service'])
                ->setVerifyRegistrationView($verifyRegistrationView)
                ->setResetPasswordView($resetPasswordView);

            return $service;
        });

        $app['user.controller'] = $app->share(function () use ($app) {
            $controller = new UserController();
            $controller->setUserService($app['user.service']);
            return $controller;
        });

        $app['verify-registration.controller'] = $app->share(function () use ($app) {
            $controller = new VerifyRegistrationController();
            $controller->setUserService($app['user.service']);
            return $controller;
        });

        $app['reset-password.controller'] = $app->share(function () use ($app) {
            $controller = new ResetPasswordController();
            $controller->setUserService($app['user.service']);
            return $controller;
        });

        $app->match('/users', 'user.controller:rest')
            ->method('HEAD|POST')
            ->bind('user-collection');

        $app->match('/users/{id}', 'user.controller:rest')
            ->method('GET|PUT')
            ->bind('user-entity');

        $app->match('/users/{id}/verify-registration', 'verify-registration.controller:rest')
            ->method('POST')
            ->bind('verify-registration');

        $app->match('/users/{id}/reset-password', 'reset-password.controller:rest')
            ->method('POST|PUT')
            ->bind('reset-password');

        $this->setFirewalls($app);
    }

    /**
     * Perform chores on boot. (None required here.)
     *
     * @param  Application $app
     */
    public function boot(Application $app)
    {
        // Noop
    }

    /**
     * Set user related firewalls
     *
     * @param Application $app
     */
    protected function setFirewalls(Application $app)
    {
        $app->extend('security.firewalls', function ($firewalls, $app) {
            $createUser = new RequestMatcher('^/users$', null, ['POST']);

            $userFirewalls = [
                'create-users' => [
                    'pattern'   => $createUser, // User registration endpoint is public
                    'anonymous' => true,
                ],
            ];

            return array_merge($userFirewalls, $firewalls);
        });
    }
}
