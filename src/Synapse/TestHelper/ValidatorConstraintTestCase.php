<?php

namespace Synapse\TestHelper;

use PHPUnit_Framework_TestCase;
use Symfony\Component\Validator\ConstraintValidator;

abstract class ValidatorConstraintTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Captured constraint violations added by the validator
     *
     * @var array[
     *      message
     *      params
     *      invalidValue
     *      plural
     *      code
     * ]
     */
    protected $violations = [];

    public function setUpMocksOnValidator(ConstraintValidator $validator)
    {
        $this->setUpMockExecutionContext();
        $this->setUpMockConstraint();

        $validator->initialize($this->mockExecutionContext);
    }

    /**
     * Set up a mock execution context which captures violations
     */
    public function setUpMockExecutionContext()
    {
        $this->mockExecutionContext = $this->getMock('Symfony\Component\Validator\ExecutionContextInterface');

        $callback = function ($message, array $params = [], $invalidValue = null, $plural = null, $code = null) {
            $this->violations[] = [
                'message'      => $message,
                'params'       => $params,
                'invalidValue' => $invalidValue,
                'plural'       => $plural,
                'code'         => $code,
            ];
        };

        $this->mockExecutionContext->expects($this->any())
            ->method('addViolation')
            ->will($this->returnCallback($callback));
    }

    public function setUpMockConstraint()
    {
        $this->mockConstraint = $this->getMock('Symfony\Component\Validator\Constraint');
    }

    public function assertNoViolationsAdded()
    {
        $this->assertEmpty($this->violations);
    }

    public function assertViolationAdded($message, array $params = [], $value = null, $plural = null, $code = null)
    {
        $violation = [
            'message'      => $message,
            'params'       => $params,
            'invalidValue' => $value,
            'plural'       => $plural,
            'code'         => $code,
        ];

        $this->assertContains($violation, $this->violations);
    }
}
