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

        foreach ($violationData as $data) {
            list($path, $message) = $data;
            $violations[] = $this->createMockConstraintViolation($path, $message);
        }

        return new ConstraintViolationList($violations);
    }

    public function assertInputYieldsOutput(array $violations, array $expectedResponse)
    {
        $violationList = $this->createConstraintViolationList($violations);

        $this->assertEquals(
            $expectedResponse,
            $this->formatter->groupViolationsByField($violationList)
        );
    }

    public function testGroupViolationsByFieldFormatsErrorsCorrectly()
    {
        $violations = [
            ['[foo]', 'bar'],
            ['[baz]', 'qux'],
        ];

        $expectedResponse = [
            'foo' => ['bar'],
            'baz' => ['qux'],
        ];

        $this->assertInputYieldsOutput($violations, $expectedResponse);
    }

    public function testGroupViolationsByFieldFormatsNestedErrorsCorrectly()
    {
        $violations = [
            ['[foo][1][account_id]', 'FOO'],
            ['[foo][1][account_name]', 'BAR'],
            ['[foo][2][amount]', 'BAZ'],
            ['[foo][2][account_id]', 'QUX'],
            ['[foo][2][account_name]', 'DONE'],
            ['[foo][2][account_name]', 'DONE2'],
        ];

        $expectedResponse = [
            'foo' => [
                null,
                [
                    'account_id'   => ['FOO'],
                    'account_name' => ['BAR'],
                ],
                [
                    'amount'       => ['BAZ'],
                    'account_id'   => ['QUX'],
                    'account_name' => ['DONE', 'DONE2'],
                ]
            ],
        ];

        $this->assertInputYieldsOutput($violations, $expectedResponse);
    }

    public function testGroupViolationsByFieldFormatsMultipleErrorsForSameFieldCorrectly()
    {
        $violations = [
            ['[foo]', 'baz'],
            ['[foo]', 'bar'],
            ['[one][two]', 'qux'],
            ['[one][two]', 'other'],
        ];

        $expectedResponse = [
            'foo' => ['baz', 'bar'],
            'one' => [
                'two' => ['qux', 'other']
            ],
        ];

        $this->assertInputYieldsOutput($violations, $expectedResponse);
    }

    public function testGroupViolationsByFieldFormatsArraysCorrectly()
    {
        $violations = [
            ['[1][foo]', 'baz'],
            ['[1][foo]', 'bar'],
            ['[1][one][two]', 'qux'],
            ['[2][one][two]', 'other'],
        ];

        $expectedResponse = [
            null,
            [
                'foo' => ['baz', 'bar'],
                'one' => ['two' => ['qux']],
            ],
            [
                'one' => ['two' => ['other']]
            ]
        ];

        $this->assertInputYieldsOutput($violations, $expectedResponse);
    }

    public function testGroupViolationsDoesNotBlowUpWhenNonsensicalViolationPathsExist()
    {
        $violations = [
            ['[foo]', 'root-level'],
            ['[foo][bar]', 'nested'],
            ['[foo][bar]', 'also-nested'],
            ['[foo][baz]', 'lone'],
            ['[foo][1]', 'numerical'],
            ['[foo][1][baz]', 'inside-numerical'],
        ];

        $expectedResponse = [
            'foo' => [
                'bar' => [
                    'nested',
                    'also-nested',
                ],
                'baz' => ['lone'],
                '0'   => 'root-level',
                '1'   => [
                    'numerical',
                    'baz' => ['inside-numerical']
                ]
            ]
        ];

        $this->assertInputYieldsOutput($violations, $expectedResponse);
    }
}
