<?php

namespace Test\Synapse\Validator\Constraints;

use Synapse\TestHelper\ValidatorConstraintTestCase;
use Synapse\Validator\Constraints\RowNotExistsValidator;
use Test\Synapse\Entity\GenericEntity;

class RowNotExistsValidatorTest extends ValidatorConstraintTestCase
{
    public function setUp()
    {
        $this->validator = new RowNotExistsValidator;

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
        $this->mockConstraint->filterCallback = function () use ($wheres) {
            return $wheres;
        };
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

    public function testValidateAddsNoViolationsIfEntityNotFound()
    {
        $this->withEntityNotFound();
        $this->withFilterCallbackReturningWheres();

        $this->validateWithValue('foo');

        $this->assertNoViolationsAdded();
    }

    public function testValidateAddsViolationIfEntityFound()
    {
        $value = 'foo';

        $this->withEntityFound();
        $this->withFilterCallbackReturningWheres();

        $this->validateWithValue($value);

        $params = [
            '{{ field }}' => 'id',
            '{{ value }}' => $value,
        ];

        $this->assertViolationAdded(
            'Entity must not exist with {{ field }} field equal to {{ value }}.',
            $params,
            $value
        );
    }

    public function testValidateSearchesForEntityByFieldSetInConstraint()
    {
        $field = 'foo';
        $value = 'bar';

        $wheres = ['x' => 'y'];
        $this->withFilterCallbackReturningWheres($wheres);

        $this->expectingEntitySearchedForWithWheres($wheres);

        $this->validateWithValue($value);
    }
}
