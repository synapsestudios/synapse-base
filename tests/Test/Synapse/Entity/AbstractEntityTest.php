<?php

namespace Test\Synapse\Entity;

use PHPUnit_Framework_TestCase;
use Synapse\Entity\AbstractEntity;
use ReflectionClass;

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

    public function testExchangeArrayDerivesSetterMethodNamesCorrectlyForMultipleWordProperties()
    {
        $expected = 'f00.b4r';

        $this->entity->exchangeArray([
            'two_word_property' => $expected
        ]);

        $reflectionObject = new ReflectionClass($this->entity);

        $reflectionProperty = $reflectionObject->getProperty('object');
        $reflectionProperty->setAccessible(true);
        $entityObject = $reflectionProperty->getValue($this->entity);

        $this->assertEquals(
            $expected,
            $entityObject['two_word_property']
        );
    }
}
