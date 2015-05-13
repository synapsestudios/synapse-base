<?php

namespace Synapse\User;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface as ExecutionContext;
use Synapse\Entity\AbstractEntity;
use Synapse\Validator\AbstractArrayValidator;
use Synapse\Security\SecurityAwareInterface;
use Synapse\Security\SecurityAwareTrait;

class UserValidator extends AbstractArrayValidator implements SecurityAwareInterface
{
    use SecurityAwareTrait;

    const BLANK = 'BLANK';

    /**
     * $contextEntity is the currently logged in user's UserEntity
     *
     * {@inheritDoc}
     */
    protected function getConstraints(array $contextData, AbstractEntity $contextEntity = null)
    {
        $notBlank = new Assert\NotBlank(['message' => self::BLANK]);

        $constraints = [
            'email' => new Assert\Optional([
                $notBlank,
                new Assert\Email([
                    'checkHost' => true
                ]),
            ]),
            'password' => new Assert\Optional([$notBlank])
        ];

        if (isset($contextData['email']) or isset($contextData['password'])) {
            $constraints['current_password'] = [
                $notBlank,
                new Assert\Callback(['callback' => [$this, 'validateCurrentPassword']]),
            ];
        }

        return $constraints;
    }

    /**
     * Validate whether the current password is correct (meant to be used in a Callback constraint)
     *
     * Adds violation to $context if it's wrong
     *
     * @param  string           $password The current password
     * @param  ExecutionContext $context
     */
    public function validateCurrentPassword($password, ExecutionContext $context)
    {
        if (! password_verify($password, $this->getUser()->getPassword())) {
            $context->addViolation('INVALID');
        }
    }
}
