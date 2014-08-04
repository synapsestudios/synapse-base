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
        $constraints = [
            'email' => new Assert\Optional([
                new Assert\Email([
                    'checkHost' => true
                ]),
            ]),
            'password' => new Assert\Optional([
                new Assert\NotBlank(),
            ])
        ];

        if (isset($contextData['email']) or isset($contextData['password'])) {
            $constraints['current_password'] = new Assert\NotBlank();
        }

        return $constraints;
    }
}
