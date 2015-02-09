<?php

namespace Synapse\TestHelper;

use Synapse\Db\TransactionAwareInterface;

trait InjectMockTransactionTrait
{
    protected $mockTransaction;

    public function injectMockTransaction(TransactionAwareInterface $object)
    {
        $this->mockTransaction = $this->getMockBuilder('Synapse\Db\Transaction')
            ->disableOriginalConstructor()
            ->getMock();

        $object->setTransaction($this->mockTransaction);
    }
}
