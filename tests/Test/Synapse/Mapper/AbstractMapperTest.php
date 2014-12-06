<?php

namespace Test\Synapse\Mapper;

use Synapse\TestHelper\MapperTestCase;
use Synapse\User\UserEntity;
use ReflectionClass;

class AbstractMapperTest extends MapperTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mapper = new Mapper($this->mockAdapter);

        $this->mapper->setSqlFactory($this->mockSqlFactory);
    }

    public function createPrototype()
    {
        return new UserEntity();
    }

    public function createEntity()
    {
        return new UserEntity([
            'id'       => 100,
            'email'    => 'address@a.com',
            'password' => 'password',
        ]);
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

    /**
     * The AbstractMapper includes a provision for backwards compatibility in getSqlObject, an internal method
     * of the class
     *
     * This method tests the backwards compatibility by making sure that no exception is thrown when no
     * SqlFactory has been set on the mapper
     */
    public function testGetSqlObjectDoesNotThrowExceptionIfSqlFactoryNotSet()
    {
        $this->mapper->findById(1);

        // Perform a dummy assertion so that this test does not appear as "risky"
        $this->assertTrue(true);
    }

    public function testPersistCallsUpdateIfEntityHasId()
    {
        $entity = $this->createEntity();

        $this->mapper->persist($entity);

        $this->assertRegExpOnSqlString('/UPDATE/');
    }

    public function testPersistCallsInsertIfEntityDoesNotHaveId()
    {
        $entity = $this->createEntity()->setId(null);

        $this->mapper->persist($entity);

        $this->assertRegExpOnSqlString('/INSERT/');
    }

    /**
     * On initialize, the hydrator is set.
     *
     * Use reflection to indirectly test that initialize only runs once even if called twice
     * by testing that the hydrator has not changed.
     */
    public function testInitializeOnlyEverRunsOnce()
    {
        $reflectionObject = new ReflectionClass($this->mapper);

        $hydrator1 = $reflectionObject->getProperty('hydrator');
        $hydrator1->setAccessible(true);

        $this->mapper->__construct($this->mockAdapter);

        $hydrator2 = $reflectionObject->getProperty('hydrator');
        $hydrator2->setAccessible(true);

        $this->assertSame(
            $hydrator1->getValue($hydrator1),
            $hydrator2->getValue($hydrator2)
        );
    }
}
