<?php

namespace Synapse\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Synapse\Controller\OAuthController;
use Synapse\OAuth2\Storage\ZendDb as OAuth2ZendDb;
use Synapse\OAuth2\ResponseType\AccessToken;
use Synapse\OAuth2\Mapper\AccessToken as AccessTokenMapper;
use Synapse\OAuth2\Mapper\RefreshToken as RefreshTokenMapper;
use Synapse\OAuth2\Entity\AccessToken as AccessTokenEntity;
use Synapse\OAuth2\Entity\RefreshToken as RefreshTokenEntity;

use OAuth2\HttpFoundationBridge\Response as BridgeResponse;
use OAuth2\Server as OAuth2Server;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\UserCredentials;
use OAuth2\ResponseType\AuthorizationCode as AuthorizationCodeResponse;

class OAuth2ServerServiceProvider implements ServiceProviderInterface
{
    public function setup(Application $app)
    {
        $app['oauth.storage'] = $app->share(function () use ($app) {
            // Create the storage object
            $storage = new OAuth2ZendDb($app['db']);
            $storage->setUserMapper($app['user.mapper']);

            return $storage;
        });

        $app['oauth_server'] = $app->share(function () use ($app) {
            $storage = $app['oauth.storage'];

            $grantTypes = [
                'authorization_code' => new AuthorizationCode($storage),
                'user_credentials'   => new UserCredentials($storage),
            ];

            $accessTokenResponseType = new AccessToken($storage, $storage);
            $authCodeResponseType = new AuthorizationCodeResponse($storage);

            return new OAuth2Server(
                $storage,
                [
                    'enforce_state'  => false,
                    'allow_implicit' => true,
                ],
                $grantTypes,
                [
                    'token' => $accessTokenResponseType,
                    'code'  => $authCodeResponseType,
                ]
            );
        });

        $app['oauth.controller'] = $app->share(function () use ($app) {
            return new OAuthController(
                $app['oauth_server'],
                $app['user.service'],
                $app['oauth-access-token.mapper'],
                $app['oauth-refresh-token.mapper'],
                $app['mustache'],
                $app['session']
            );
        });

        $app['oauth-access-token.mapper'] = $app->share(function () use ($app) {
            return new AccessTokenMapper($app['db'], new AccessTokenEntity);
        });

        $app['oauth-refresh-token.mapper'] = $app->share(function () use ($app) {
            return new RefreshTokenMapper($app['db'], new RefreshTokenEntity);
        });
    }

    public function register(Application $app)
    {
        $this->setup($app);

        $app->get('/oauth/authorize', 'oauth.controller:authorize');
        $app->get('/oauth/authorize-submit', 'oauth.controller:authorizeFormSubmit')
            ->bind('oauth-authorize-form-submit');

        $app->post('/oauth/token', 'oauth.controller:token');
        $app->post('/logout', 'oauth.controller:logout');
    }

    public function boot(Application $app)
    {
    }
}
