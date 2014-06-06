<?php

namespace Synapse\Db\Adapter\Driver\Mysqli;

use Zend\Db\Adapter\Driver\Mysqli\Connection as MysqliConnection;

/**
 * Simulate nested transactions
 */
class NestedTransactionConnection extends MysqliConnection
{
    /**
     * The current transaction depth
     *
     * @var integer
     */
    protected $transactionDepth = 0;

    /**
     * {@inheritdoc}
     */
    public function beginTransaction()
    {
        if ($this->transactionDepth === 0) {
            parent::beginTransaction();
        }

        $this->transactionDepth += 1;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        if ($this->transactionDepth === 1) {
            parent::commit();
        }

        $this->transactionDepth -= 1;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
        if ($this->transactionDepth === 1) {
            parent::rollback();
        }

        $this->transactionDepth -= 1;

        return $this;
    }
}
