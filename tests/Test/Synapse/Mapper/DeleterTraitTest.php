<?php

namespace Test\Synapse\Mapper;

use Synapse\TestHelper\MapperTestCase;
use Synapse\User\UserEntity;

class DeleterTraitTest extends MapperTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->prototype = $this->createPrototype();

        $this->mapper = new Mapper($this->mockAdapter, $this->prototype);
        $this->mapper->setSqlFactory($this->mockSqlFactory);
    }

    public function createPrototype()
    {
        return new UserEntity();
    }

    public function createEntityToDelete()
    {
        return new UserEntity([
            'id'         => 12345,
            'email'      => 'test@example.com',
            'password'   => 'password',
            'last_login' => time(),
            'created'    => time(),
            'enabled'    => '0',
            'verified'   => '0',
        ]);
    }

    public function createDeletionConstraints()
    {
        return [
            'foo'  => 'bar',
            'baz'  => 1.99,
            'qux'  => null,
            'life' => 42,
        ];
    }

    public function createWhereClauseRegexp($constraints)
    {
        $whereValues = [];

        foreach ($constraints as $field => $value) {
            if ($value === null) {
                $whereValue = sprintf('`%s` IS NULL', $field);
            } else {
                $whereValue = sprintf('`%s` = \'%s\'', $field, $value);
            }

            $whereValues[] = $whereValue;
        }

        $whereValueString = implode(' AND ', $whereValues);

        return sprintf('/WHERE %s/', $whereValueString);
    }

    public function testDeleteDeletesFromCorrectTable()
    {
        $tableName = $this->mapper->getTableName();

        $entity = $this->createEntityToDelete();

        $this->mapper->delete($entity);

        $regexp = sprintf('/DELETE FROM `%s`/', $tableName);

        $this->assertRegExp($regexp, $this->getSqlString());
    }

    public function testDeleteDeletesWhereIdIsIdOfEntity()
    {
        $entity = $this->createEntityToDelete();

        $regexp = sprintf('/WHERE `id` = \'%s\'/', $entity->getId());

        $this->mapper->delete($entity);

        $this->assertRegExp($regexp, $this->getSqlString());
    }

    public function testDeleteWhereConstructsWhereClausesCorrectly()
    {
        $constraints = $this->createDeletionConstraints();

        $regexp = $this->createWhereClauseRegexp($constraints);

        $this->mapper->deleteWhere($constraints);

        $this->assertRegExp($regexp, $this->getSqlString());
    }
}
