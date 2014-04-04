<?php

namespace Test\Synapse\Entity;

use PHPUnit_Framework_TestCase;
use Synapse\Entity\AbstractEntity;

class AbstractEntityTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->entity = new GenericEntity;
    }

    public function testCanGetAndSetProperties()
    {
        $this->entity->setFoo('bar');

        $this->assertEquals('bar', $this->entity->getFoo());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionThrownIfPropertyDoesNotExistForSetter()
    {
        $this->entity->setNonExistentProperty('foo');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionThrownIfPropertyDoesNotExistForGetter()
    {
        $this->entity->getNonExistentProperty();
    }

    public function testCanGetDefaultValues()
    {
        $this->assertEquals(1, $this->entity->getDefault1());
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testMagicCallMethodOnlyAcceptsGettersAndSetters()
    {
        $this->entity->notAGetterOrSetter('foo');
    }
}
