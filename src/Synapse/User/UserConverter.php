<?php

namespace Synapse\User;

use Symfony\Component\HtpFoundation\Request;
use Synapse\User\UserMapper;

class UserConverter
{
    protected $userMapper;

    public function __construct(UserMapper $userMapper)
    {
        $this->userMapper = $userMapper;
    }

    public function getUser($userId)
    {
        return $this->userMapper->findById($userId);
    }
}
