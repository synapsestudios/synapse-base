<?php

namespace Test\Synapse\Validator\Constraints;

use PHPUnit_Framework_TestCase;
use Synapse\Validator\Constraints\RowExists;

class RowExistsTest extends PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        $this->mockMapper = $this->getMockBuilder('Test\Synapse\Mapper\Mapper')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testFilterCallbackIsCreatedEvenIfNoFieldOrCallbackProvided()
    {
        $constraint = new RowExists($this->mockMapper);

        $this->assertNotNull($constraint->getFilterCallback());
    }
}
