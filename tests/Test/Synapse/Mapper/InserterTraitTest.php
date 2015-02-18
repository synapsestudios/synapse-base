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

        $this->datetimeMapper = new DatetimeColumnMapper($this->mockAdapter, $this->timestampPrototype);
        $this->datetimeMapper->setSqlFactory($this->mockSqlFactory);

        $this->invalidAutoincrementMapper = new InvalidAutoincrementMapper(
            $this->mockAdapter,
            $this->timestampPrototype
        );
        $this->invalidAutoincrementMapper->setSqlFactory($this->mockSqlFactory);
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

        $this->assertRegExpOnSqlString($regexp);
    }

    public function testInsertInsertsCorrectValues()
    {
        $entity = $this->createEntityToInsert();

        // $arrayCopy = $entity->getArrayCopy();
        $arrayCopy = $entity->getDbValues();

        $regexp = $this->createInsertClauseRegexp($arrayCopy);

        $this->mapper->insert($entity);

        $this->assertRegExpOnSqlString($regexp);
    }

    public function testInsertSetsCreatedTimestampColumnAutomaticallyOnEntityAndDbQuery()
    {
        $entity = $this->createTimestampEntityToInsert()
            ->setCreated(null);

        $this->datetimeMapper->insert($entity);

        $regexp = sprintf(
            '/\(`id`, `foo`, `created`, `updated`\) VALUES \(NULL, \'bar\', \'%s+\', NULL\)/',
            '(\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2})' // datetime pattern
        );

        $this->assertRegExpOnSqlString($regexp);

        $this->assertNotNull($entity->getCreated());
    }

    public function testInsertSetsCreatedDatetimeColumnAutomaticallyOnEntityAndDbQuery()
    {
        $entity = $this->createTimestampEntityToInsert()
            ->setCreated(null);

        $this->timestampMapper->insert($entity);

        $regexp = sprintf('/\(`id`, `foo`, `created`, `updated`\) VALUES \(NULL, \'bar\', \'[0-9]+\', NULL\)/');

        $this->assertRegExpOnSqlString($regexp);

        $this->assertNotNull($entity->getCreated());
    }

    public function testInsertSetsGeneratedAutoIncrementIdOnEntityIfEntityDoesNotAlreadyHaveAnId()
    {
        $entity     = $this->createEntityToInsert();
        $expectedId = self::GENERATED_ID;

        $this->mapper->insert($entity);

        $this->assertEquals($expectedId, $entity->getId());
    }

    public function testInsertDoesNotSetIdOnEntityIfEntityAlreadyHasAnId()
    {
        $entity     = $this->createEntityToInsert();
        $expectedId = self::GENERATED_ID + 1;

        $entity->setId($expectedId);
        $this->mapper->insert($entity);

        $this->assertEquals($expectedId, $entity->getId());
    }

    public function testInsertThrowsExceptionIfAutoincrementColumnNotInEntity()
    {
        $this->setExpectedException('Synapse\Mapper\Exception');

        $entity = $this->createEntityToInsert();

        $this->invalidAutoincrementMapper->insert($entity);
    }
}
