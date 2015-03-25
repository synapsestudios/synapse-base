<?php

namespace Test\Synapse\Validator\Constraints;

use Synapse\Validator\Constraints\RowNotExistsValidator;

class RowNotExistsValidatorTest extends AbstractRowExistsTestCase
{
    const MESSAGE = 'Entity must not exist with {{ field }} field equal to {{ value }}.';

    public function setUp()
    {
        $this->validator = new RowNotExistsValidator;

        parent::setUp();
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
        $this->mockConstraint->message = self::MESSAGE;

        $this->validateWithValue($value);

        $params = [
            '{{ field }}' => 'id',
            '{{ value }}' => $value,
        ];

        $this->assertViolationAdded(
            self::MESSAGE,
            $params,
            $value
        );
    }
}
