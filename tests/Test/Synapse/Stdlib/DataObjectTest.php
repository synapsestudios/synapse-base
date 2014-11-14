<?php

namespace Test\Synapse\Stdlib;

use PHPUnit_Framework_TestCase;

class DataObjectTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->dataObject = new TestDataObject();
    }

    public function testStringSetterOverrideSetsValueAsString()
    {
        $this->dataObject->setString(1);

        $this->assertEquals('1', $this->dataObject->getString());
    }

    public function testIntegerSetterOverrideSetsValueAsInteger()
    {
        $this->dataObject->setInteger('1');

        $this->assertEquals(1, $this->dataObject->getInteger());
    }

    public function testBooleanSetterOverrideSetsValueAsBoolean()
    {
        $this->dataObject->setBoolean(1);

        $this->assertEquals(true, $this->dataObject->getBoolean());
    }
}
