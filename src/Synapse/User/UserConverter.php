<?php

namespace Synapse\User;

use Symfony\Component\HtpFoundation\Request;
use Synapse\User\UserMapper;

/**
 * Converter for turning a user id into a UserEntity
 */
class UserConverter
{
    /**
     * @var UserMapper
     */
    protected $userMapper;

    /**
     * @param UserMapper $userMapper
     */
    public function __construct(UserMapper $userMapper)
    {
        $this->userMapper = $userMapper;
    }

    /**
     * Convert a user ID into a UserEntity
     * @param  string          $userId
     * @return UserEntity|bool
     */
    public function getUser($userId)
    {
        return $this->userMapper->findById($userId);
    }
}
