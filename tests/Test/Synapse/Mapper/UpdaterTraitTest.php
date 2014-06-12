<?php

namespace Test\Synapse\Mapper;

use Synapse\TestHelper\MapperTestCase;
use Synapse\User\UserEntity;

class UpdaterTraitTest extends MapperTestCase
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

    public function createEntityToUpdate()
    {
        return new UserEntity([
            'id'         => 1234,
            'email'      => 'test@example.com',
            'password'   => 'password',
            'last_login' => time(),
            'created'    => time(),
            'enabled'    => '0',
            'verified'   => '0',
        ]);
    }

    public function testUpdateUpdatesIntoCorrectTable()
    {
        $tableName = $this->mapper->getTableName();

        $entity = $this->createEntityToUpdate();

        $this->mapper->update($entity);

        $regexp = sprintf('/UPDATE `%s` SET /', $tableName);

        $this->assertRegExp($regexp, $this->getSqlString());
    }

    public function testUpdateUpdatesWhereIdIsEntityId()
    {
        $tableName = $this->mapper->getTableName();

        $entity = $this->createEntityToUpdate();

        $regexp = sprintf('/WHERE `id` = \'%s\'/', $entity->getId());

        $this->mapper->update($entity);

        $this->assertRegExp($regexp, $this->getSqlString());
    }

    public function testUpdateUpdatesToCorrectValues()
    {
        $entity = $this->createEntityToUpdate();

        // $arrayCopy = $entity->getArrayCopy();
        $arrayCopy = $entity->getDbValues();

        // Query should not try to update the ID, even to the same ID
        unset($arrayCopy['id']);

        $updateValues = [];

        foreach ($arrayCopy as $key => $value) {
            if ($value === null) {
                $value = 'NULL';
            } else {
                $value = "'$value'";
            }

            $updateValues[] = sprintf('`%s` = %s', $key, $value);
        }

        $regexp = sprintf(
            '/%s/',
            implode(', ', $updateValues)
        );

        $this->mapper->update($entity);

        $this->assertRegExp($regexp, $this->getSqlString());
    }
}
