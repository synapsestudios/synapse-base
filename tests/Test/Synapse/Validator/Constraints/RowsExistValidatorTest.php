<?php

namespace Test\Synapse\Validator\Constraints;

use Synapse\TestHelper\ValidatorConstraintTestCase;
use Synapse\Validator\Constraints\RowsExistValidator;
use Test\Synapse\Entity\GenericEntity;

class RowsExistValidatorTest extends ValidatorConstraintTestCase
{
    const MESSAGE = 'Entity must exist with {{ field }} field equal to {{ value }}.';

    public function setUp()
    {
        $this->validator = new RowsExistValidator;

        $this->setUpMocksOnValidator($this->validator);

        $this->setUpMapperInMockConstraint();
    }

    public function setUpMockConstraint()
    {
        $this->mockConstraint = $this->getMockBuilder('Synapse\Validator\Constraints\RowsExist')
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

    public function expectingEntitySearchedForWithWheres($at, $wheres)
    {
        $this->mockMapper->expects($this->at($at))
            ->method('findBy')
            ->with($this->equalTo($wheres));
    }

    public function validateWithValues($values)
    {
        return $this->validator->validate($values, $this->mockConstraint);
    }

    public function testValidateAddsNoViolationsIfEntityFound()
    {
        $this->withEntityFound();
        $this->withFilterCallbackReturningWheres();

        $this->validateWithValues(['foo', 'bar', 'baz']);

        $this->assertNoViolationsAdded();
    }

    public function testValidateAddsViolationIfEntityNotFound()
    {
        $values = ['foo', 'bar', 'baz'];

        $this->withEntityNotFound();
        $this->withFilterCallbackReturningWheres();
        $this->mockConstraint->message = self::MESSAGE;

        $this->validateWithValues($values);

        $params = [];

        foreach ($values as $value) {
            $params[] = [
                '{{ field }}' => 'id',
                '{{ value }}' => $value,
            ];
        }

        foreach ($values as $key => $value) {
            $this->assertViolationAdded(
                self::MESSAGE,
                $params[$key],
                $value
            );
        }
    }

    public function testValidateSearchesForEntityByFieldSetInConstraint()
    {
        $field = 'foo';
        $values = ['bar', 'baz', 'qux'];

        $this->mockConstraint->filterCallback = function ($value) use ($field) {
            return [$field => $value];
        };

        foreach ($values as $key => $value) {
            $this->expectingEntitySearchedForWithWheres($key, [$field => $value]);
        }

        $this->validateWithValues($values);
    }
}
