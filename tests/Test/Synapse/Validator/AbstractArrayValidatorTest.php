<?php

namespace Test\Synapse\Validator;

use Synapse\TestHelper\ArrayValidatorTestCase;
use Synapse\Validator\AbstractArrayValidator;

class AbstractArrayValidatorTest extends ArrayValidatorTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->validator = new ArrayValidator($this->symfonyValidator);
    }

    public function testMissingFieldsReturnCorrectMessage()
    {
        $errors = $this->validator->validate([]);

        $this->assertEquals(AbstractArrayValidator::MISSING, $errors->get(0)->getMessage());
    }
}
