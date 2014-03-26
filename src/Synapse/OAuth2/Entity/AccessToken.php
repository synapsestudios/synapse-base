<?php

namespace Synapse\OAuth2\Entity;

use Synapse\Entity\AbstractEntity;

class AccessToken extends AbstractEntity
{
    protected $object = [
        'access_token' => null,
        'client_id'    => null,
        'user_id'      => null,
        'expires'      => null,
        'scope'        => null,
    ];
}
