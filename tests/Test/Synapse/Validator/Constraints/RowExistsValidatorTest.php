<?php

namespace Test\Synapse\Validator\Constraints;

use Synapse\Validator\Constraints\RowExistsValidator;

class RowExistsValidatorTest extends AbstractRowExistsTestCase
{
    const MESSAGE = 'Entity must exist with {{ field }} field equal to {{ value }}.';

    public function setUp()
    {
        $this->validator = new RowExistsValidator;

        parent::setUp();
    }

    public function testValidateAddsNoViolationsIfEntityFound()
    {
        $this->withEntityFound();
        $this->withFilterCallbackReturningWheres();
        $this->validateWithValue('foo');
        $this->assertNoViolationsAdded();
    }

    public function testValidateAddsViolationIfEntityNotFound()
    {
        $value = 'foo';

        $this->withEntityNotFound();
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
