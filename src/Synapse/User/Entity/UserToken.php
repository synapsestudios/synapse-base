<?php

namespace Synapse\User\Entity;

use Synapse\Entity\AbstractEntity;

/**
 * User token entity used for registration verification among other purposes
 */
class UserToken extends AbstractEntity
{
    // Token types
    const TYPE_VERIFY_REGISTRATION = 'Verify registration';
    const TYPE_RESET_PASSWORD      = 'Reset password';

    /**
     * {@inheritDoc}
     */
    protected $object = [
        'id'      => null,
        'user_id' => null,
        'token'   => null,
        'type'    => null,
        'created' => null,
        'expires' => null,
    ];

    /**
     * Generate a random string to use as a token
     *
     * @return string
     */
    public function generateToken()
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }
}
