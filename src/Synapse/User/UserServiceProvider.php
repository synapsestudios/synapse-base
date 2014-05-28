<?php

namespace Synapse\User;

use Synapse\User\TokenEntity;
use Synapse\User\TokenMapper;
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
        $app['user.mapper'] = $app->share(function ($app) {
            return new UserMapper($app['db'], new UserEntity);
        });

        $app['user-token.mapper'] = $app->share(function ($app) {
            return new TokenMapper($app['db'], new TokenEntity);
        });

        $app['user-role-pivot.mapper'] = $app->share(function ($app) {
            return new UserRolePivotMapper($app['db']);
        });

        $app['role.service'] = $app->share(function ($app) {
            return new RoleService($app['user-role-pivot.mapper']);
        });

        $app['user.service'] = $app->share(function ($app) {
            $verifyRegistrationView = new VerifyRegistrationView($app['mustache']);
            $verifyRegistrationView->setUrlGenerator($app['url_generator']);

            $service = new UserService;
            $service->setUserMapper($app['user.mapper'])
                ->setTokenMapper($app['user-token.mapper'])
                ->setEmailService($app['email.service'])
                ->setVerifyRegistrationView($verifyRegistrationView);

            return $service;
        });

        $app['user.validator'] = $app->share(function ($app) {
            return new UserValidator($app['validator']);
        });

        $app['user.controller'] = $app->share(function ($app) {
            $controller = new UserController();
            $controller
                ->setUserService($app['user.service'])
                ->setUserValidator($app['user.validator']);
            return $controller;
        });

        $app['user.converter'] = $app->share(function ($app) {
            return new UserConverter($app['user.mapper']);
        });

        $app['verify-registration.controller'] = $app->share(function ($app) {
            $controller = new VerifyRegistrationController();
            $controller->setUserService($app['user.service']);
            return $controller;
        });

        $app['reset-password.controller'] = $app->share(function ($app) {
            $resetPasswordView = new ResetPasswordView($app['mustache']);

            $resetPasswordView->setUrlGenerator($app['url_generator']);

            return new ResetPasswordController(
                $app['user.service'],
                $app['email.service'],
                $resetPasswordView
            );
        });

        $app->match('/users', 'user.controller:rest')
            ->method('HEAD|POST')
            ->bind('user-collection');

        $app->match('/user', 'user.controller:rest')
            ->method('GET|PUT')
            ->bind('user-entity-self');

        $app->match('/users/{user}', 'user.controller:rest')
            ->method('GET')
            ->bind('user-entity')
            ->assert('user', '\d+')
            ->convert('user', 'user.converter:getUser');

        $app->match('/users/{id}/verify-registration', 'verify-registration.controller:rest')
            ->method('POST')
            ->bind('verify-registration');

        $app->match('/user/reset-password', 'reset-password.controller:rest')
            ->method('POST|PUT')
            ->bind('reset-password');

        $this->setFirewallsAndAccessRules($app);
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
    protected function setFirewallsAndAccessRules(Application $app)
    {
        $app->extend('security.firewalls', function ($firewalls, $app) {
            $createUser = new RequestMatcher('^/users$', null, ['POST']);
            $verifyRegistration = new RequestMatcher('^/users/[0-9]+/verify-registration$', null, ['POST']);

            $userFirewalls = [
                'create-users' => [
                    'pattern'   => $createUser, // User registration endpoint is public
                    'anonymous' => true,
                ],
                'verify-registration' => [
                    'pattern'   => $verifyRegistration, // User registration endpoint is public
                    'anonymous' => true,
                ],
                'reset-password' => [
                    'pattern'   => '^/user/reset-password$',
                    'anonymous' => true,
                ],
            ];

            return array_merge($userFirewalls, $firewalls);
        });

        $app->extend('security.access_rules', function ($rules, $app) {
            $usersAdminFunctionRequestMatcher = new RequestMatcher('^/users/\d+$', null, ['GET']);
            $newRules = [
                [$usersAdminFunctionRequestMatcher, 'ROLE_ADMIN']
            ];
            return array_merge($newRules, $rules);
        });
    }
}
