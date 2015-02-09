<?php

namespace Synapse\Db;

interface TransactionAwareInterface
{
    /**
     * Set transaction object
     *
     * @param Transaction $transaction
     */
    public function setTransaction(Transaction $transaction);
}
