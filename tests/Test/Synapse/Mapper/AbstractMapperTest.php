<?php

namespace Test\Synapse\Mapper;

use Synapse\TestHelper\MapperTestCase;
use Synapse\User\UserEntity;

class AbstractMapperTest extends MapperTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mapper = new Mapper($this->mockAdapter);
    }

    public function createPrototype()
    {
        return new UserEntity();
    }

    public function testGetPrototypeReturnsCloneOfPrototype()
    {
        $prototype = $this->createPrototype();

        $this->mapper->setPrototype($prototype);

        // Assert that the given prototype and the retrieved one are not references to the same object
        $this->assertNotSame($prototype, $this->mapper->getPrototype());

        // Demonstrate that a clone was provided by comparing the array copies
        $this->assertEquals(
            $prototype->getArrayCopy(),
            $this->mapper->getPrototype()->getArrayCopy()
        );
    }
}
