<?php

namespace Test\Synapse\Validator\Constraints;

use Synapse\TestHelper\ValidatorConstraintTestCase;
use Test\Synapse\Entity\GenericEntity;

abstract class AbstractRowExistsTestCase extends ValidatorConstraintTestCase
{
    public function setUp()
    {
        $this->setUpMocksOnValidator($this->validator);
        $this->setUpMockConstraint();
        $this->setUpMapperInMockConstraint();
    }

    public function setUpMockConstraint()
    {
        $this->mockConstraint = $this->getMockBuilder('Synapse\Validator\Constraints\RowNotExists')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function setUpMapperInMockConstraint()
    {
        $this->mockMapper = $this->getMockBuilder('Test\Synapse\Mapper\Mapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockConstraint->expects($this->any())
            ->method('getMapper')
            ->will($this->returnValue($this->mockMapper));
    }

    public function withFilterCallbackReturningWheres($wheres = [])
    {
        $callback = function () use ($wheres) {
            return $wheres;
        };

        $this->mockConstraint->expects($this->any())
            ->method('getFilterCallback')
            ->will($this->returnValue($callback));
    }

    public function withEntityFound()
    {
        $entity = new GenericEntity;

        $this->mockMapper->expects($this->any())
            ->method('findBy')
            ->will($this->returnValue($entity));
    }

    public function withEntityNotFound()
    {
        $this->mockMapper->expects($this->any())
            ->method('findBy')
            ->will($this->returnValue(false));
    }

    public function expectingEntitySearchedForWithWheres($wheres)
    {
        $this->mockMapper->expects($this->once())
            ->method('findBy')
            ->with($this->equalTo($wheres));
    }

    public function validateWithValue($value)
    {
        return $this->validator->validate($value, $this->mockConstraint);
    }
}
