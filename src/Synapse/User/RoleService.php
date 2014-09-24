<?php

namespace Synapse\User;

use Synapse\User\RoleFinderInterface;

/**
 * Service to perform tasks related to user roles
 */
class RoleService implements RoleFinderInterface
{
    /**
     * @var RoleFinderInterface
     */
    protected $roleFinder;

    /**
     * @param RoleFinderInterface $roleFinder
     */
    public function __construct(RoleFinderInterface $roleFinder)
    {
        $this->roleFinder = $roleFinder;
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
        return $this->roleFinder->findRoleNamesByUserId($userId);
    }
}
