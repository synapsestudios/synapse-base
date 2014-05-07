<?php

namespace Synapse\User;

use Synapse\Entity\AbstractEntity;

/**
 * User token entity used for registration verification among other purposes
 */
class TokenEntity extends AbstractEntity
{
    // Token types
    const TYPE_VERIFY_REGISTRATION = 1;
    const TYPE_RESET_PASSWORD      = 2;

    /**
     * {@inheritDoc}
     */
    protected $object = [
        'id'            => null,
        'user_id'       => null,
        'token'         => null,
        'token_type_id' => null,
        'created'       => null,
        'expires'       => null,
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
