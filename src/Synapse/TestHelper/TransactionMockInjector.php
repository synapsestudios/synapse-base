<?php

namespace Synapse\TestHelper;

use Synapse\Db\TransactionAwareInterface;

trait TransactionMockInjector
{
    public function injectMockTransaction(TransactionAwareInterface $object)
    {
        $this->mocks['transaction'] = $this->getMockBuilder('Synapse\Db\Transaction')
            ->disableOriginalConstructor()
            ->getMock();

        $object->setTransaction($this->mocks['transaction']);
    }
}
