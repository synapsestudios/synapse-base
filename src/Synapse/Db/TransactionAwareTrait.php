<?php

namespace Synapse\Db;

trait TransactionAwareTrait
{
    /**
     * @var Transaction;
     */
    protected $transaction;

    public function setTransaction(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
