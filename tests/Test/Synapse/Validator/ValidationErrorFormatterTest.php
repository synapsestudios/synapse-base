<?php

namespace Test\Synapse\Validator;

use PHPUnit_Framework_TestCase;
use Synapse\Validator\ValidationErrorFormatter;
use Symfony\Component\Validator\ConstraintViolationList;

class ValidationErrorFormatterTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->formatter = new ValidationErrorFormatter;
    }

    public function createMockConstraintViolation($path, $message)
    {
        $mock = $this->getMockBuilder('Symfony\Component\Validator\ConstraintViolation')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('getPropertyPath')
            ->will($this->returnValue($path));

        $mock->expects($this->any())
            ->method('getMessage')
            ->will($this->returnValue($message));

        return $mock;
    }

    public function createConstraintViolationList(array $violationData)
    {
        $violations = [];

        foreach ($violationData as $path => $message) {
            $violations[] = $this->createMockConstraintViolation($path, $message);
        }

        return new ConstraintViolationList($violations);
    }

    public function testValidationErrorFormatterFormatsErrorsCorrectly()
    {
        $violations = [
            '[foo]' => 'bar',
            '[baz]' => 'qux',
        ];

        $violationList = $this->createConstraintViolationList($violations);

        $expectedResponse = [
            'foo' => ['bar'],
            'baz' => ['qux'],
        ];

        $this->assertEquals(
            $expectedResponse,
            $this->formatter->groupViolationsByField($violationList)
        );
    }
}
