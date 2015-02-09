<?php

namespace Synapse\Db;

interface TransactionAwareInterface
{
    /**
     * Set transaction object
     *
     * @param Transation $transaction
     */
    public function setTransaction(Transaction $transaction);
}
