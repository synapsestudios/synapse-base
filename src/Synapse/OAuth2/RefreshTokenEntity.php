<?php

namespace Synapse\OAuth2;

use Synapse\Entity\AbstractEntity;

class RefreshTokenEntity extends AbstractEntity
{
    protected $object = [
        'refresh_token' => null,
        'client_id'     => null,
        'user_id'       => null,
        'expires'       => null,
        'scope'         => null,
    ];
}
