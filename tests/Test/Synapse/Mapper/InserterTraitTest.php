<?php

namespace Test\Synapse\Mapper;

use Synapse\TestHelper\MapperTestCase;
use Synapse\User\UserEntity;

class InserterTraitTest extends MapperTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->prototype          = $this->createPrototype();
        $this->timestampPrototype = $this->createTimestampPrototype();

        $this->mapper = new Mapper($this->mockAdapter, $this->prototype);
        $this->mapper->setSqlFactory($this->mockSqlFactory);

        $this->timestampMapper = new TimestampColumnMapper($this->mockAdapter, $this->timestampPrototype);
        $this->timestampMapper->setSqlFactory($this->mockSqlFactory);
    }

    public function createPrototype()
    {
        return new UserEntity();
    }

    public function createTimestampPrototype()
    {
        return new TimestampColumnEntity();
    }

    public function createEntityToInsert()
    {
        return new UserEntity([
            'email'      => 'test@example.com',
            'password'   => 'password',
            'last_login' => time(),
            'created'    => time(),
            'enabled'    => '0',
            'verified'   => '0',
        ]);
    }

    public function createTimestampEntityToInsert()
    {
        return new TimestampColumnEntity([
            'id'      => null,
            'foo'     => 'bar',
            'created' => null,
            'updated' => null,
        ]);
    }

    public function createInsertClauseRegexp($arrayCopy)
    {
        $columns = sprintf(
            '\(`%s`\)',
            implode('`, `', array_keys($arrayCopy))
        );

        $insertValues = [];

        foreach ($arrayCopy as $key => $value) {
            if ($value === null) {
                $value = 'NULL';
            } else {
                $value = "'$value'";
            }

            $insertValues[$key] = $value;
        }

        $values = sprintf(
            '\(%s\)',
            implode(', ', $insertValues)
        );

        return sprintf('/%s VALUES %s/', $columns, $values);
    }

    public function testInsertInsertsIntoCorrectTable()
    {
        $tableName = $this->mapper->getTableName();

        $entity = $this->createEntityToInsert();

        $this->mapper->insert($entity);

        $regexp = sprintf('/INSERT INTO `%s`/', $tableName);

        $this->assertRegExp($regexp, $this->getSqlString());
    }

    public function testInsertInsertsCorrectValues()
    {
        $entity = $this->createEntityToInsert();

        // $arrayCopy = $entity->getArrayCopy();
        $arrayCopy = $entity->getDbValues();

        $regexp = $this->createInsertClauseRegexp($arrayCopy);

        $this->mapper->insert($entity);

        $this->assertRegExp($regexp, $this->getSqlString());
    }

    public function testInsertSetsCreatedTimestampColumnAutomatically()
    {
        $entity = $this->createTimestampEntityToInsert()
            ->setCreated(null);

        $this->timestampMapper->insert($entity);

        $regexp = sprintf('/\(`id`, `foo`, `created`, `updated`\) VALUES \(NULL, \'bar\', \'[0-9]+\', NULL\)/');

        $this->assertRegExp($regexp, $this->getSqlString());

        $this->assertNotNull($entity->getCreated());
    }
}
