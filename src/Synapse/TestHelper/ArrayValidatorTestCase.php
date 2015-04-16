<?php

namespace Synapse\TestHelper;

use Symfony\Component\Validator\Validator;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\DefaultTranslator;
use Symfony\Component\Validator\Mapping\ClassMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;

abstract class ArrayValidatorTestCase extends TestCase
{
    public function setUp()
    {
        $this->symfonyValidator = new Validator(
            new ClassMetadataFactory(new StaticMethodLoader()),
            new ConstraintValidatorFactory(),
            new DefaultTranslator()
        );
    }
}
