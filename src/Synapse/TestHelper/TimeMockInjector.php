<?php

namespace Synapse\TestHelper;

use Synapse\Time\TimeAwareInterface;

trait TimeMockInjector
{
    public function injectMockTimeObject(TimeAwareInterface $injectee)
    {
        $this->mocks['time'] = $this->getMock('Synapse\Time\Time');

        $injectee->setTimeObject($this->mocks['time']);
    }
}
