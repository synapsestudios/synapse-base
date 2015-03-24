<?php

namespace Test\Synapse\Validator\Constraints;

use PHPUnit_Framework_TestCase;
use Synapse\Validator\Constraints\RowNotExists;

class RowNotExistsTest extends PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        $this->mockMapper = $this->getMockBuilder('Test\Synapse\Mapper\Mapper')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testDefaultMessageIsSetIfFieldProvidedButNoMessageProvided()
    {
        $constraint = new RowNotExists($this->mockMapper, ['field' => 'foo']);

        $this->assertEquals(RowNotExists::FIELD_MESSAGE, $constraint->message);
    }
}
