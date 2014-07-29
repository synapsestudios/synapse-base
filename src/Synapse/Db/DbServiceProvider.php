<?php

namespace Synapse\Db;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Synapse\Db\Adapter\Adapter;
use Synapse\Db\Transaction;
use Zend\Db\Sql\Sql;

/**
 * Provider for Zend database services
 */
class DbServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the database adapter
     *
     * @param  Application $app Silex application
     */
    public function register(Application $app)
    {
        $app['db'] = $app->share(function ($app) {
            return new Adapter($app['config']->load('db'));
        });

        $app['db.transaction'] = $app->share(function ($app) {
            return new Transaction($app['db']);
        });
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
