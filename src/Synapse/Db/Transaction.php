<?php

namespace Synapse\Db;

use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Adapter\Driver\ConnectionInterface;

/**
 * Gateway for starting, committing and rolling back database transactions
 */
class Transaction
{
    /**
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * The current transaction depth
     *
     * @var integer
     */
    protected $transactionDepth = 0;

    /**
     * @param DbAdapter $dbAdapter Query builder object
     */
    public function __construct(DbAdapter $dbAdapter)
    {
        $this->connection = $dbAdapter->getDriver()->getConnection();
    }

    /**
     * Begin a database transaction
     */
    public function begin()
    {
        if ($this->transactionDepth === 0) {
            $this->connection->beginTransaction();
        }

        $this->transactionDepth += 1;

        return $this;
    }

    /**
     * Commit the open database transaction
     */
    public function commit()
    {
        if ($this->transactionDepth === 1) {
            $this->connection->commit();
        }

        $this->transactionDepth -= 1;

        return $this;
    }

    /**
     * Rollback the open database transaction
     */
    public function rollback()
    {
        if ($this->transactionDepth === 1) {
            $this->connection->rollback();
        }

        $this->transactionDepth -= 1;

        return $this;
    }
}
