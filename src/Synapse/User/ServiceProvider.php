<?php

namespace Synapse\User;

use Synapse\User\Controller\UserController;
use Synapse\User\Controller\VerifyRegistrationController;
use Synapse\User\Controller\ResetPasswordController;
use Synapse\User\Entity\User as UserEntity;
use Synapse\User\Entity\UserToken as UserTokenEntity;
use Synapse\User\Mapper\User as UserMapper;
use Synapse\User\Mapper\UserToken as UserTokenMapper;
use Synapse\View\Email\VerifyRegistration as VerifyRegistrationView;
use Synapse\View\Email\ResetPassword as ResetPasswordView;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Service provider for user related services
 */
class ServiceProvider implements ServiceProviderInterface
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
            return new UserTokenMapper($app['db'], new UserTokenEntity);
        });

        $app['user.service'] = $app->share(function () use ($app) {
            $verifyRegistrationView = new VerifyRegistrationView($app['mustache']);
            $verifyRegistrationView->setUrlGenerator($app['url_generator']);

            $resetPasswordView = new ResetPasswordView($app['mustache']);
            $resetPasswordView->setUrlGenerator($app['url_generator']);

            $service = new UserService;
            $service->setUserMapper($app['user.mapper'])
                ->setUserTokenMapper($app['user-token.mapper'])
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
    }

    /**
     * Perform chores on boot. (None required here.)
     *
     * @param  Application $app
     */
    public function boot(Application $app)
    {
        // noop
    }
}
