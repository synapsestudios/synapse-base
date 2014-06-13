<?php

namespace Synapse\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Role;

use Synapse\Mapper;
use Synapse\Entity\AbstractEntity;
use Synapse\Stdlib\Arr;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;

/**
 * User mapper
 */
class UserMapper extends Mapper\AbstractMapper implements UserProviderInterface
{
    /**
     * Use all mapper traits, making this a general purpose mapper
     */
    use Mapper\InserterTrait;
    use Mapper\FinderTrait;
    use Mapper\UpdaterTrait;
    use Mapper\DeleterTrait;

    /**
     * Name of user table
     *
     * @var string
     */
    protected $tableName = 'users';

    /**
     * Find user by email
     *
     * @param  string $email
     * @return UserEntity
     */
    public function findByEmail($email)
    {
        $entity = $this->findBy(['email' => $email]);
        return $entity;
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        return $this->findByEmail($username);
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->findById($user->getId());
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return true;
    }
}
