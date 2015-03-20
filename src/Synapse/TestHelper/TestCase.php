<?php

namespace Synapse\TestHelper;

use PHPUnit_Framework_TestCase;

class TestCase extends PHPUnit_Framework_TestCase
{
    protected $mocks = [];

    public function setMocks(array $mocks)
    {
        foreach ($mocks as $alias => $class) {
            $this->mocks[$alias] = $this->getMockBuilder($class)
                ->disableOriginalConstructor()
                ->getMock();
        }
    }
}
