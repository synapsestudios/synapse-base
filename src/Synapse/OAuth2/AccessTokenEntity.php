<?php

namespace Synapse\OAuth2;

use Synapse\Entity\AbstractEntity;

class AccessTokenEntity extends AbstractEntity
{
    protected $object = [
        'access_token' => null,
        'client_id'    => null,
        'user_id'      => null,
        'expires'      => null,
        'scope'        => null,
    ];
}
