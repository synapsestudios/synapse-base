<?php

namespace Synapse\User;

use Symfony\Component\Validator\Constraints as Assert;
use Synapse\Entity\AbstractEntity;
use Synapse\Validator\AbstractArrayValidator;

class UserValidator extends AbstractArrayValidator
{
    /**
     * {@inheritDoc}
     */
    protected function getConstraints(array $contextData, AbstractEntity $contextEntity = null)
    {
        return [
            'email' => new Assert\Email([
                'checkHost' => true
            ]),
            'password' => new Assert\NotBlank(),
        ];
    }
}
