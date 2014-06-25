<?php

namespace Synapse\OAuth2;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Synapse\Security\Authentication\OAuth2Provider;
use Synapse\Security\Firewall\OAuth2Listener;
use Synapse\Security\Firewall\OAuth2OptionalListener;

class SecurityServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Application $app)
    {
        $app['security.authentication_listener.factory.oauth'] = $app->protect(function ($name, $options) use ($app) {
            $app['security.authentication_provider.'.$name.'.oauth'] = $app->share(function ($app) {
                return new OAuth2Provider(
                    $app['user.mapper'],
                    $app['role.service'],
                    $app['oauth_server']
                );
            });

            $app['security.authentication_listener.'.$name.'.oauth'] = $app->share(function ($app) {
                return new OAuth2Listener($app['security'], $app['security.authentication_manager']);
            });

            return [
                'security.authentication_provider.'.$name.'.oauth',
                'security.authentication_listener.'.$name.'.oauth',
                null,
                'pre_auth'
            ];
        });

        $app['security.authentication_listener.factory.oauth-optional'] = $app->protect(function ($name, $options) use ($app) {
            $app['security.authentication_provider.'.$name.'.oauth-optional'] = $app->share(function ($app) {
                return new OAuth2Provider(
                    $app['user.mapper'],
                    $app['role.service'],
                    $app['oauth_server']
                );
            });

            $app['security.authentication_listener.'.$name.'.oauth-optional'] = $app->share(function ($app) {
                return new OAuth2OptionalListener($app['security'], $app['security.authentication_manager']);
            });

            return [
                'security.authentication_provider.'.$name.'.oauth',
                'security.authentication_listener.'.$name.'.oauth',
                null,
                'pre_auth'
            ];
        });
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
    }
}
