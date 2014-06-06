<?php

namespace Synapse\Db\Adapter;

use Synapse\Db\Adapter\Driver\Mysqli\NestedTransactionConnection as MysqliNestedTransactionConnection;
use Zend\Db\Adapter\Adapter as ZendDbAdapter;

/**
 * Uses a Mysqli connection that supports pseudo-nested transactions.
 */
class Adapter extends ZendDbAdapter
{
    /**
     * {@inheritdoc}
     */
    protected function createDriver($parameters)
    {
        $driver = parent::createDriver($parameters);

        if ($driver instanceof \Zend\Db\Adapter\Mysqli\Mysqli) {
            $connection = $driver->getConnection();

            $nestedTransactionConnection = new MysqliNestedTransactionConnection(
                $connection->getConnectionParameters()
            );

            $driver->registerConnection($nestedTransactionConnection);
        }

        return $driver;
    }
}
