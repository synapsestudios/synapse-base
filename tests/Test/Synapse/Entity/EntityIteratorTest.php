<?php

namespace Test\Synapse\Entity;

use PHPUnit_Framework_TestCase;
use Synapse\Entity\EntityIterator;

class EntityIteratorTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        foreach (range(1, 10) as $id) {
            $this->entities[] = new GenericEntity(array('foo' => $id));
        }

        $this->iterator = new EntityIterator($this->entities);
    }

    public function testGetEntitiesReturnsCorrectArray()
    {
        $this->assertEquals($this->entities, $this->iterator->getEntities());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetEntitiesThrowsExceptionForInvalidEntityTypes()
    {
        $entities = array(
            new GenericEntity,
            new GenericEntity,
            array()
        );

        $iterator = new EntityIterator($entities);
    }

    public function testIterationWorksCorrectly()
    {
        $ids = array();
        foreach ($this->iterator as $i) {
            $ids[] = $i->getFoo();
        }

        $this->assertEquals(range(1, 10), $ids);

        // Double check that we can iterate twice, which can sometimes be an issue
        // with iterators of certain types
        $ids = array();
        foreach ($this->iterator as $i) {
            $ids[] = $i->getFoo();
        }

        $this->assertEquals(range(1, 10), $ids);
    }
}
