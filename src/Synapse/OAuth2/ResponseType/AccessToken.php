<?php

namespace Synapse\OAuth2\ResponseType;

use OAuth2\ResponseType\AccessToken as BaseAccessToken;

class AccessToken extends BaseAccessToken
{
    public function createAccessToken($clientId, $userId, $scope = null, $includeRefreshToken = true)
    {
        $token = parent::createAccessToken($clientId, $userId, $scope, $includeRefreshToken);
        $token['user_id'] = $userId;

        return $token;
    }
}
