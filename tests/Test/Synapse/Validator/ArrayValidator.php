<?php

namespace Test\Synapse\Validator;

use Synapse\Validator\AbstractArrayValidator;
use Synapse\Entity\AbstractEntity;
use Symfony\Component\Validator\Constraints\NotBlank;

class ArrayValidator extends AbstractArrayValidator
{
    /**
     * {@inheritdoc}
     */
    protected function getConstraints(array $contextData, AbstractEntity $contextEntity = null)
    {
        return [
            'foo' => new NotBlank()
        ];
    }
}
