<?php

namespace Synapse\User\Mapper;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Role;

use Synapse\Mapper;
use Synapse\Entity\AbstractEntity;
use Synapse\User\Entity\User as UserEntity;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;

/**
 * User mapper
 */
class User extends Mapper\AbstractMapper implements UserProviderInterface
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

    public function findByEmail($email)
    {
        $entity = $this->findBy(['email' => $email]);
        return $entity;
    }

    public function findRolesByUser(UserEntity $user)
    {
        $sql = new Sql($this->dbAdapter);

        $select = new Select('pvt_roles_users');
        $select->columns([])
            ->join('user_roles', 'user_roles.id = pvt_roles_users.role_id', ['name'])
            ->where(['user_id' => $user->getId()]);

        $statement = $sql->prepareStatementForSqlObject($select);

        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());

        $results = $resultSet->toArray();

        $return = [];
        foreach ($results as $row) {
            $return[] = $row['name'];
        }

        return $return;
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        $entity = $this->findByEmail($username);
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user)
    {
        $entity = $this->findById($user->getId());
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return true;
    }
}
