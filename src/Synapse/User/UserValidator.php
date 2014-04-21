<?php

namespace Synapse\User;

use Synapse\Validator\AbstractArrayValidator;
use Symfony\Component\Validator\Constraints as Assert;

class UserValidator extends AbstractArrayValidator
{
    /**
     * {@inheritDoc}
     */
    public function getConstraints()
    {
        return [
            'email' => new Assert\Email([
                'checkHost' => true
            ]),
            'password' => new Assert\NotBlank(),
        ];
    }
}
