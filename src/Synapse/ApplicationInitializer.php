<?php

namespace Synapse;

use Synapse\Provider\ConfigServiceProvider;
use Symfony\Component\Debug\Debug;

/**
 * Object for initializing the Silex application
 */
class ApplicationInitializer
{
    /**
     * Application version
     *
     * @var string
     */
    protected $appVersion = '0.0.0';

    /**
     * Initialize the Silex Application
     *
     * Register services and routes
     * Set basic properties
     *
     * @return Synapse\Application
     */
    public function initialize()
    {
        // Create the application object
        $app = new Application;

        // Store application version
        $app['version'] = $this->appVersion;

        $this->setEnvironment($app);
        $this->registerConfig($app);

        // Handle init config
        $initConfig = $app['config']->load('init');

        if ($initConfig['debug']) {
            Debug::enable();
            $app['debug'] = true;
        }

        return $app;
    }

    /**
     * Set $app['environment'] based on $_SERVER['APP_ENV']
     *
     * @param Application $app
     */
    protected function setEnvironment(Application $app)
    {
        // Define acceptable environments
        $environments = array(
            'production',
            'staging',
            'qa',
            'testing',
            'development',
        );

        // Detect the current application environment
        if (isset($_SERVER['APP_ENV']) && in_array($_SERVER['APP_ENV'], $environments)) {
            $app['environment'] = $_SERVER['APP_ENV'];
        } else {
            $app['environment'] = 'development';
        }
    }

    /**
     * Register the config service
     *
     * Config is a bit of a special-case service provider and needs to be
     * registered before all the others (so that they can access it)
     *
     * @param  Application $app
     */
    protected function registerConfig(Application $app)
    {
        $app->register(new ConfigServiceProvider(), array(
            'config_dirs' => array(
                APPDIR.'/config/',
                APPDIR.'/config/'.$app['environment'].'/',
            ),
        ));
    }
}
