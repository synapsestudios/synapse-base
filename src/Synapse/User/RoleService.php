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
     * Add a role to a given user
     *
     * @param UserEntity $user
     * @param string     $role name of the role to add
     */
    public function addRoleForUser(UserEntity $user, $role)
    {
        $roles = $user->getRoles();

        if (! in_array($role, $roles)) {
            $this->userRolePivotMapper->addRoleForUser($user->getId(), $role);

            array_push($roles, $role);

            $user->setRoles($roles);
        }
    }
}
