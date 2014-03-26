<?php

namespace Synapse\OAuth2\Entity;

use Synapse\Entity\AbstractEntity;

class RefreshToken extends AbstractEntity
{
    protected $object = [
        'refresh_token' => null,
        'client_id'     => null,
        'user_id'       => null,
        'expires'       => null,
        'scope'         => null,
    ];
}
