<?php

namespace Synapse\User;

/**
 * Service for tasks relating to user roles
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
     * Add a list of roles to a given user
     *
     * @param UserEntity $user
     * @param array      $roles an array of strings
     */
    public function addRolesForUser(UserEntity $user, array $roles)
    {
        foreach ($roles as $role) {
            $this->userRolePivotMapper->addRoleForUser($user->getId(), $role);
        }
    }
}
