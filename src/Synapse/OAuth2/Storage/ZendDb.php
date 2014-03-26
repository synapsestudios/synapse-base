<?php

namespace Synapse\OAuth2\Storage;

use Synapse\Stdlib\Arr;

use OAuth2\Storage\AuthorizationCodeInterface;
use OAuth2\Storage\AccessTokenInterface;
use OAuth2\Storage\ClientCredentialsInterface;
use OAuth2\Storage\UserCredentialsInterface;
use OAuth2\Storage\RefreshTokenInterface;

use Synapse\User\Mapper\User as UserMapper;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\PreparableSqlInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Expression;

class ZendDb implements
    AuthorizationCodeInterface,
    AccessTokenInterface,
    ClientCredentialsInterface,
    UserCredentialsInterface,
    RefreshTokenInterface
{
    protected $userMapper;

    protected $adapter;

    protected $config;

    public function __construct(Adapter $adapter, $config = array())
    {
        $this->adapter = $adapter;

        $this->config = array_merge(array(
            'client_table'        => 'oauth_clients',
            'access_token_table'  => 'oauth_access_tokens',
            'refresh_token_table' => 'oauth_refresh_tokens',
            'code_table'          => 'oauth_authorization_codes',
            'user_table'          => 'oauth_users',
            'jwt_table'           => 'oauth_jwt',
        ), $config);
    }

    public function checkClientCredentials($clientId, $clientSecret = null)
    {
        $select = new Select($this->config['client_table']);
        $select->where(array('client_id' => $clientId));

        $result = $this->execute($select)->current();
        return Arr::get($result, 'client_secret', null) === $clientSecret;
    }

    public function getClientDetails($clientId)
    {
        $select = new Select($this->config['client_table']);
        $select->where(array('client_id' => $clientId));

        return $this->execute($select)->current();
    }

    public function checkRestrictedGrantType($clientId, $grantType)
    {
        $details = $this->getClientDetails($clientId);
        if (isset($details['grant_types'])) {
            $grantTypes = explode(' ', $details['grant_types']);

            return in_array($grantType, (array) $grantTypes);
        }

        // if grant_types are not defined, then none are restricted
        return true;
    }

    /* OAuth2_Storage_AccessTokenInterface */
    public function getAccessToken($accessToken)
    {
        $select = new Select($this->config['access_token_table']);
        $select->columns(array(
            '*',
            'expires' => new Expression('UNIX_TIMESTAMP(expires)')
        ))->where(array('access_token' => $accessToken));

        return $this->execute($select)->current();
    }

    public function setAccessToken($accessToken, $clientId, $userId, $expires, $scope = null)
    {
        $expires = date('Y-m-d H:i:s', $expires);

        if ($this->getAccessToken($accessToken)) {
            $update = new Update($this->config['access_token_table']);
            $update->set(array(
                'client_id' => $clientId,
                'user_id'   => $userId,
                'expires'   => $expires,
                'scope'     => $scope,
            ))->where(array(
                'access_token' => $accessToken,
            ));

            return $this->execute($update);
        } else {
            $insert = new Insert($this->config['access_token_table']);
            $insert->values(array(
                'access_token' => $accessToken,
                'client_id'    => $clientId,
                'user_id'      => $userId,
                'expires'      => $expires,
                'scope'        => $scope,
            ));

            return $this->execute($insert);
        }
    }

    /* OAuth2_Storage_AuthorizationCodeInterface */
    public function getAuthorizationCode($code)
    {
        $select = new Select($this->config['code_table']);
        $select->columns(array(
            '*',
            'expires' => new Expression('UNIX_TIMESTAMP(expires)')
        ))->where(array('authorization_code' => $code));

        return $this->execute($select)->current();
    }

    public function setAuthorizationCode($code, $clientId, $userId, $redirectUri, $expires, $scope = null)
    {
        $expires = date('Y-m-d H:i:s', $expires);

        if ($this->getAuthorizationCode($code)) {
            $update = new Update($this->config['code_table']);
            $update->set(array(
                'client_id'    => $clientId,
                'user_id'      => $userId,
                'redirect_uri' => $redirectUri,
                'expires'      => $expires,
                'scope'        => $scope,
            ))->where(array(
                'code' => $code,
            ));

            return $this->execute($update);
        } else {
            $insert = new Insert($this->config['code_table']);
            $insert->values(array(
                'authorization_code' => $code,
                'client_id'          => $clientId,
                'user_id'            => $userId,
                'expires'            => $expires,
                'scope'              => $scope,
            ));

            return $this->execute($insert);
        }
    }

    public function expireAuthorizationCode($code)
    {
        $delete = new Delete($this->config['code_table']);
        $delete->where(array('authorization_code' => $code));

        return $this->execute($delete);
    }

    /* OAuth2_Storage_UserCredentialsInterface */
    public function checkUserCredentials($username, $password)
    {
        if ($user = $this->getUser($username)) {
            return $this->checkPassword($user, $password);
        }

        return false;
    }

    /* OAuth2_Storage_RefreshTokenInterface */
    public function getRefreshToken($refreshToken)
    {
        $select = new Select($this->config['refresh_token_table']);
        $select->columns(array(
            '*',
            'expires' => new Expression('UNIX_TIMESTAMP(expires)')
        ))->where(array('refresh_token' => $refreshToken));

        return $this->execute($select)->current();
    }

    public function setRefreshToken($refreshToken, $clientId, $userId, $expires, $scope = null)
    {
        $expires = date('Y-m-d H:i:s', $expires);

        if ($this->getRefreshToken($refreshToken)) {
            $update = new Update($this->config['refresh_token_table']);
            $update->set(array(
                'client_id' => $clientId,
                'user_id'   => $userId,
                'expires'   => $expires,
                'scope'     => $scope,
            ))->where(array(
                'refresh_token' => $refreshToken,
            ));

            return $this->execute($update);
        } else {
            $insert = new Insert($this->config['refresh_token_table']);
            $insert->values(array(
                'refresh_token' => $refreshToken,
                'client_id'    => $clientId,
                'user_id'      => $userId,
                'expires'      => $expires,
                'scope'        => $scope,
            ));

            return $this->execute($insert);
        }
    }

    public function unsetRefreshToken($refreshToken)
    {
        $delete = new Delete($this->config['refresh_token_table']);
        $delete->where(array('refresh_token' => $refreshToken));

        return $this->execute($delete);
    }

    public function setUser($username, $password, $firstName = null, $lastName = null)
    {
        throw new \LogicException('Not implemented');
    }

    /**
     * Set the user mapper
     * @param UserMapper $userMapper
     */
    public function setUserMapper(UserMapper $userMapper)
    {
        $this->userMapper = $userMapper;
        return $this;
    }

    /**
     * Get the user as an array
     *
     * @param  string $email the user's email address
     * @return array
     */
    public function getUserDetails($email)
    {
        $user = $this->getUser($email);
        return array(
            'user_id' => $user->getId(),
        );
    }

    /**
     * Get the user to check their credentials
     *
     * @param  string $email the user's email address
     * @return Synapse\Entity\User
     */
    public function getUser($email)
    {
        $user = $this->userMapper->findByEmail($email);

        if (!$user) {
            return false;
        }

        return $user;
    }

    /**
     * Verify the user's password hash
     * @param  Synapse\Entity\User $user     the user to check the given password against
     * @param  string $password the password to check
     * @return boolean whether the password is valid
     */
    public function checkPassword($user, $password)
    {
        return password_verify($password, $user->getPassword());
    }

    protected function execute(PreparableSqlInterface $query)
    {
        $sql = new Sql($this->adapter);
        return $sql->prepareStatementForSqlObject($query)
            ->execute();
    }
}
