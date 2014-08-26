<?php

namespace Test\Synapse\Validator\Constraints;

use Synapse\TestHelper\ValidatorConstraintTestCase;
use Synapse\Validator\Constraints\RowExistsValidator;
use Test\Synapse\Entity\GenericEntity;

class RowExistsValidatorTest extends ValidatorConstraintTestCase
{
    public function setUp()
    {
        $this->validator = new RowExistsValidator;

        parent::setUp($this->validator);

        $this->setUpMapperInMockConstraint();
    }

    public function setUpMockConstraint()
    {
        $this->mockConstraint = $this->getMockBuilder('Synapse\Validator\Constraints\RowExists')
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

    public function validateWithValue($value)
    {
        return $this->validator->validate($value, $this->mockConstraint);
    }

    public function testValidateAddsNoViolationsIfEntityFound()
    {
        $this->withEntityFound();

        $this->validateWithValue('foo');

        $this->assertNoViolationsAdded();
    }

    public function testValidateAddsViolationIfEntityNotFound()
    {
        $value = 'foo';

        $this->withEntityNotFound();

        $this->validateWithValue($value);

        $params = [
            '{{ field }}' => 'id',
            '{{ value }}' => $value,
        ];

        $this->assertViolationAdded(
            'Entity must exist with {{ field }} field equal to {{ value }}.',
            $params,
            $value
        );
    }
}
