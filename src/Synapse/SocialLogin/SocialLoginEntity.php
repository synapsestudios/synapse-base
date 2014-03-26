<?php

namespace Synapse\SocialLogin;

use Synapse\Entity\AbstractEntity;

/**
 * User entity
 */
class SocialLoginEntity extends AbstractEntity
{
    /**
     * {@inheritDoc}
     */
    protected $object = [
        'id'                   => null,
        'user_id'              => null,
        'provider'             => null,
        'provider_user_id'     => null,
        'access_token'         => null,
        'access_token_expires' => null,
        'refresh_token'        => null,
    ];
}
