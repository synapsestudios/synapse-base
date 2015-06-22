<?php
namespace Synapse\TestHelper;

use Application;
use Silex\Provider\SessionServiceProvider;
use Silex\WebTestCase as WebCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernel;
use Synapse;

class WebTestCase extends WebCase
{
    protected $mocks = [];

    protected $authToken = [
        'client_id' => 123,
        'user_id'   => 42,
        'scope'     => '',
    ];

    public function setUp()
    {
        // Don't call createApplication by default
    }

    public function setMocks(array $mocks)
    {
        foreach ($mocks as $alias => $class) {
            $this->mocks[$alias] = $this
                ->getMockBuilder($class)
                ->disableOriginalConstructor()
                ->getMock();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createClient(array $server = array())
    {
        return new WebTestClient($this->app, $server);
    }

    public function createApplication()
    {
        throw new \Exception('Use createApplicationWithServices instead');
    }

    /**
     * Creates the application.
     *
     * @return Synapse\Application
     */
    public function createApplicationWithServices(array $services)
    {
        defined('WEBDIR') or define('WEBDIR', realpath(__DIR__));
        defined('APPDIR') or define('APPDIR', realpath(WEBDIR.'/..'));
        defined('DATADIR') or define('DATADIR', APPDIR.'/data');
        defined('TMPDIR') or define('TMPDIR', '/tmp');
        date_default_timezone_set('UTC');

        $applicationInitializer = new Synapse\ApplicationInitializer;

        $app = $applicationInitializer->initialize();

        $app->register(new \Silex\Provider\SecurityServiceProvider);
        $app->register(new \Synapse\Controller\ControllerServiceProvider);
        $app->register(new \Silex\Provider\UrlGeneratorServiceProvider);

        $app['security.firewalls'] = $app->share(function () {
            return [
                'base.api' => [
                    'pattern' => '^/',
                    'oauth'   => true,
                ],
            ];
        });

        $app['security.access_rules'] = $app->share(function () {
            return [];
        });

        // synapse's default session provider doesn't allow for testing so override
        $sessionServiceProvider = new SessionServiceProvider();
        $sessionServiceProvider->register($app);

        $app['debug'] = true;
        $app['session.test'] = true;
        $app['exception_handler']->disable();

        foreach ($services as $service) {
            $app->register($service);
        }

        $this->setupOAuth2Provider($app);
        return $app;
    }

    public function getValidResponse()
    {
        $response = new Response();
        $response->setStatusCode(200);
        return $response;
    }

    public function withValidResponseFromControllerMethod($mockIndex, $method)
    {
        $this->mocks[$mockIndex]
            ->expects($this->any())
            ->method($method)
            ->will($this->returnValue($this->getValidResponse()));
    }

    protected function setupOAuth2Provider(Synapse\Application $app)
    {
        // reset oauth storage
        $app['oauth.storage'] = $this
            ->getMockBuilder('\Application\OAuth2\Storage\ZendDb')
            ->disableOriginalConstructor()
            ->getMock();

        $this->setMocks([
            'oauth2Provider'         => 'Synapse\Security\Authentication\OAuth2Provider',
            'oauth2Listener'         => 'Synapse\Security\Firewall\OAuth2Listener',
            'oauth2OptionalListener' => 'Synapse\Security\Firewall\OAuth2OptionalListener',
        ]);

        $app['security.authentication_listener.factory.oauth'] = $app->protect(function ($name, $options) use ($app) {
            $app['security.authentication_provider.'.$name.'.oauth'] = $app->share(function ($app) {
                return $this->mocks['oauth2Provider'];
            });

            $app['security.authentication_listener.'.$name.'.oauth'] = $app->share(function ($app) {
                return new Synapse\Security\Firewall\OAuth2Listener(
                    $app['security'],
                    $app['security.authentication_manager']
                );
            });

            return [
                'security.authentication_provider.'.$name.'.oauth',
                'security.authentication_listener.'.$name.'.oauth',
                null,
                'pre_auth'
            ];
        });

        $app['security.authentication_listener.factory.oauth-optional'] = $app->protect(
            function ($name, $options) use ($app) {
                $app['security.authentication_provider.'.$name.'.oauth-optional'] = $app->share(function ($app) {
                    return $this->mocks['oauth2Provider'];
                });

                $app['security.authentication_listener.'.$name.'.oauth-optional'] = $app->share(function ($app) {
                    return new Synapse\Security\Firewall\OAuth2OptionalListener(
                        $app['security'],
                        $app['security.authentication_manager']
                    );
                });

                return [
                    'security.authentication_provider.'.$name.'.oauth-optional',
                    'security.authentication_listener.'.$name.'.oauth-optional',
                    null,
                    'pre_auth'
                ];
            }
        );
    }

    /**
     * @param array $token
     */
    protected function setAuthToken(array $token)
    {
        $this->authToken = $token;
    }

    protected function withAuthenticatedRoles($roles = [])
    {
        $this->authToken['expires'] = strtotime('+1 year');
        $this->app['oauth.storage']->expects($this->any())
            ->method('getAccessToken')
            ->willReturn($this->authToken);

        $this->mocks['oauth2Provider']->expects($this->any())
            ->method('supports')
            ->willReturn(true);

        $this->mocks['oauth2Provider']->expects($this->any())
            ->method('authenticate')
            ->willReturn(new Synapse\Security\Authentication\OAuth2UserToken($roles));
    }
}
