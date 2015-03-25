<?php

namespace Test\Synapse\Validator\Constraints;

use Synapse\Validator\Constraints\RowsExistValidator;

class RowsExistValidatorTest extends AbstractRowExistsTestCase
{
    const MESSAGE = 'Entity must exist with {{ field }} field equal to {{ value }}.';

    public function setUp()
    {
        $this->validator = new RowsExistValidator;

        parent::setUp();
    }

    public function testValidateAddsNoViolationsIfEntityFound()
    {
        $this->withEntityFound();
        $this->withFilterCallbackReturningWheres();
        $this->validateWithValue(['foo', 'bar', 'baz']);
        $this->assertNoViolationsAdded();
    }

    public function testValidateAddsViolationIfEntityNotFound()
    {
        $values = ['foo', 'bar', 'baz'];

        $this->withEntityNotFound();
        $this->withFilterCallbackReturningWheres();
        $this->mockConstraint->message = self::MESSAGE;

        $this->validateWithValue($values);

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
}
