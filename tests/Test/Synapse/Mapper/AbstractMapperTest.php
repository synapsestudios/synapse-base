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

    public function createEntity()
    {
        return new UserEntity([
            'id'       => 100,
            'email'    => 'address@a.com',
            'password' => 'password',
        ]);
    }

    public function capturingSqlStrings()
    {
        $this->mapper->setSqlFactory($this->mockSqlFactory);
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
        $this->capturingSqlStrings();

        $entity = $this->createEntity();

        $this->mapper->persist($entity);

        $this->assertRegExpOnSqlString('/UPDATE/');
    }

    public function testPersistCallsInsertIfEntityDoesNotHaveId()
    {
        $this->capturingSqlStrings();

        $entity = $this->createEntity()->setId(null);

        $this->mapper->persist($entity);

        $this->assertRegExpOnSqlString('/INSERT/');
    }
}
