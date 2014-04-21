<?php

namespace Synapse\User;

use Synapse\User\UserRolePivotMapper;

/**
 * Service to perform tasks related to user roles
 */
class RoleService
{
    /**
     * @var UserRolePivotMapper
     */
    protected $userRolePivotMapper;

    /**
     * @param UserRolePivotMapper $userRolePivotMapper
     */
    public function __construct(UserRolePivotMapper $userRolePivotMapper)
    {
        $this->userRolePivotMapper = $userRolePivotMapper;
    }

    /**
     * Find roles for a given user by user ID
     *
     * Can be extended to perform more complex role-finding logic
     *
     * @param  string $userId
     * @return array          Array of role names for the given user ID
     */
    public function findRoleNamesByUserId($userId)
    {
        return $this->userRolePivotMapper->findRoleNamesByUserId($userId);
    }
}
