<?php

namespace Synapse\User;

use Synapse\Mapper;
use Synapse\Stdlib\Arr;
use Zend\Db\Sql\Expression;

/**
 * Mapper for user roles pivot table
 */
class UserRolePivotMapper extends Mapper\AbstractMapper implements RoleFinderInterface
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

    /**
     * Add a given role to the given user id
     *
     * @param int    $userId
     * @param string $roleName
     */
    public function addRoleForUser($userId, $roleName)
    {
        $roleIdExpression = new Expression(
            '(SELECT `id` from `user_roles` WHERE `name` = ?)',
            $roleName
        );

        $query = $this->getSqlObject()
            ->insert()
            ->values([
                'user_id' => $userId,
                'role_id' => $roleIdExpression,
            ]);

            $this->execute($query);
    }
}
