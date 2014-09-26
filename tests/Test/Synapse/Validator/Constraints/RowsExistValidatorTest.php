<?php

namespace Test\Synapse\Validator\Constraints;

use Synapse\TestHelper\ValidatorConstraintTestCase;
use Synapse\Validator\Constraints\RowsExistValidator;
use Test\Synapse\Entity\GenericEntity;

class RowsExistValidatorTest extends ValidatorConstraintTestCase
{
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

    public function expectingEntitySearchedForWithFieldAndValue($at, $field, $value)
    {
        $wheres = [$field => $value];

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

        $this->validateWithValues(['foo', 'bar', 'baz']);

        $this->assertNoViolationsAdded();
    }

    public function testValidateAddsViolationIfEntityNotFound()
    {
        $values = ['foo', 'bar', 'baz'];

        $this->withEntityNotFound();

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
                'Entity must exist with {{ field }} field equal to {{ value }}.',
                $params[$key],
                $value
            );
        }
    }

    public function testValidateSearchesForEntityByFieldSetInConstraint()
    {
        $field = 'foo';
        $values = ['bar', 'baz', 'qux'];

        $this->mockConstraint->field = $field;

        foreach ($values as $key => $value) {
            $this->expectingEntitySearchedForWithFieldAndValue($key, $field, $value);
        }

        $this->validateWithValues($values);
    }
}
