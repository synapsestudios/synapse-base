<?php

namespace Synapse\User;

abstract class RoleFinderDecorator implements RoleFinderInterface
{
    /**
     * @var RoleFinderInterface RoleFinder object to decorate
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
     * {@inheritDoc}
     */
    abstract public function findRoleNamesByUserId($userId);
}
