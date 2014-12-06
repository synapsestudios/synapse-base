<?php

namespace Synapse\Db;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Synapse\Db\Adapter\Adapter;
use Synapse\Db\Transaction;
use Synapse\Mapper\SqlFactory;
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

        $this->registerMapperInitializer($app);
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

    /**
     * Register an initializer that injects a SQL Factory into all AbstractMappers
     *
     * @param  Application $app
     */
    protected function registerMapperInitializer(Application $app)
    {
        $initializer = function ($mapper) {
            $mapper->setSqlFactory(new SqlFactory);
        };

        $app->initializer('Synapse\Mapper\AbstractMapper', $initializer);
    }
}
