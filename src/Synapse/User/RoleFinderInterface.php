<?php

namespace Synapse\User;

interface RoleFinderInterface
{
    /**
     * Find roles for a given user by user ID
     *
     * Can be extended to perform more complex role-finding logic
     *
     * @param  string $userId
     * @return array          Array of role names for the given user ID
     */
    public function findRoleNamesByUserId($userId);
}
