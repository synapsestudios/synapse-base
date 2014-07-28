<?php

namespace Synapse\Db;

use Zend\Db\Adapter\Adapter as DbAdapter;

/**
 * Inject this into a non-mapper class to allow it to control database transactions
 */
class Transaction
{
    protected $connection;

    public function __construct(DbAdapter $dbAdapter)
    {
        $this->connection = $dbAdapter->getDriver()->getConnection();
    }

    /**
     * Begin a database transaction
     */
    public function begin()
    {
        $this->connection->beginTransaction();
    }

    /**
     * Commit the open database transaction
     */
    public function commit()
    {
        $this->connection->commit();
    }

    /**
     * Rollback the open database transaction
     */
    public function rollback()
    {
        $this->connection->rollback();
    }
}
