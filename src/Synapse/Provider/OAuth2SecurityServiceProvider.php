<?php

namespace Synapse\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Synapse\Security\Authentication\OAuth2Provider;
use Synapse\Security\Firewall\OAuth2Listener;

class OAuth2SecurityServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['security.authentication_listener.factory.oauth'] = $app->protect(function ($name, $options) use ($app) {
            $app['security.authentication_provider.'.$name.'.oauth'] = $app->share(function () use ($app) {
                return new OAuth2Provider($app['user.mapper'], $app['oauth_server']);
            });

            $app['security.authentication_listener.'.$name.'.oauth'] = $app->share(function () use ($app) {
                return new OAuth2Listener($app['security'], $app['security.authentication_manager']);
            });

            return [
                'security.authentication_provider.'.$name.'.oauth',
                'security.authentication_listener.'.$name.'.oauth',
                null,
                'pre_auth'
            ];
        });
    }

    public function boot(Application $app)
    {
    }
}
