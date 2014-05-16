<?php

namespace Synapse\User;

use Synapse\Mapper;
use Synapse\Stdlib\Arr;

/**
 * Mapper for user roles pivot table
 */
class UserRolePivotMapper extends Mapper\AbstractMapper
{
    use Mapper\PivotInserterTrait;
    use Mapper\PivotDeleterTrait;

    /**
     * {@inheritDoc}
     */
    protected $tableName = 'pvt_roles_users';

    /**
     * Find roles for a user by user ID
     *
     * @param  string $userId
     * @return array  Array of role names
     */
    public function findRoleNamesByUserId($userId)
    {
        $query = $this->getSqlObject()
            ->select()
            ->join('user_roles', 'user_roles.id = pvt_roles_users.role_id', ['name'])
            ->where(['user_id' => $userId]);

        $results = $this->execute($query)->toArray();

        return Arr::pluck($results, 'name');
    }
}
