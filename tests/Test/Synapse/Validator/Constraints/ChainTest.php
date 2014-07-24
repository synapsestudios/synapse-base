<?php

namespace Test\Synapse\Validator\Constraints;

use PHPUnit_Framework_TestCase;
use Symfony\Component\Validator\Constraints\Valid;
use Synapse\Validator\Constraints\Chain;

class ChainTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Symfony\Component\Validator\Exception\ConstraintDefinitionException
     */
    public function testConstructorThrowsExceptionIfConstraintsOptionsIsNotAnArray()
    {
        $constraint = new Chain(['constraints' => 'foo,bar']);
    }

    /**
     * @expectedException Symfony\Component\Validator\Exception\ConstraintDefinitionException
     */
    public function testConstructorThrowsExceptionIfConstraintOptionIsNotAConstraint()
    {
        $contains = new Chain(['foo']);
    }

    /**
     * @expectedException Symfony\Component\Validator\Exception\ConstraintDefinitionException
     */
    public function testConstructorThrowsExceptionIfConstraintOptionsIncludesValid()
    {
        $contains = new Chain([new Valid()]);
    }
}
