<?php

namespace Test\Synapse\Mapper;

use Synapse\Mapper\AbstractMapper;
use Synapse\Mapper\UpdaterTrait;
use Synapse\TestHelper\MapperTestCase;
use Synapse\User\UserEntity;

class UpdaterTraitTest extends MapperTestCase
{
    private $prototype;
    private $timestampPrototype;
    /**
     * @var AbstractMapper|UpdaterTrait
     */
    protected $mapper;
    /**
     * @var AbstractMapper|UpdaterTrait
     */
    private $timestampMapper;
    /**
     * @var AbstractMapper|UpdaterTrait
     */
    private $datetimeMapper;
    /**
     * @var AbstractMapper|UpdaterTrait
     */
    private $differentPrimaryKeyMapper;

    public function setUp()
    {
        parent::setUp();

        $this->prototype = $this->createPrototype();
        $this->timestampPrototype = $this->createTimestampPrototype();

        $this->mapper = new Mapper($this->mocks['adapter'], $this->prototype);
        $this->mapper->setSqlFactory($this->mocks['sqlFactory']);

        $this->timestampMapper = new TimestampColumnMapper($this->mocks['adapter'], $this->timestampPrototype);
        $this->timestampMapper->setSqlFactory($this->mocks['sqlFactory']);

        $this->datetimeMapper = new DatetimeColumnMapper($this->mocks['adapter'], $this->timestampPrototype);
        $this->datetimeMapper->setSqlFactory($this->mocks['sqlFactory']);

        $this->differentPrimaryKeyMapper = new MapperWithDifferentPrimaryKey(
            $this->mocks['adapter'],
            $this->prototype
        );
        $this->differentPrimaryKeyMapper->setSqlFactory($this->mocks['sqlFactory']);
    }

    public function createPrototype()
    {
        return new UserEntity();
    }

    public function createTimestampPrototype()
    {
        return new TimestampColumnEntity();
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

    public function createTimestampEntityToUpdate()
    {
        return new TimestampColumnEntity([
            'id'      => 1234567,
            'foo'     => 'bar',
            'created' => time(),
            'updated' => null,
        ]);
    }

    public function createUpdateClauseRegexp($arrayCopy)
    {
        $updateValues = [];

        foreach ($arrayCopy as $key => $value) {
            if ($value === null) {
                $value = 'NULL';
            } else {
                $value = "'$value'";
            }

            $updateValues[] = sprintf('`%s` = %s', $key, $value);
        }

        return sprintf(
            '/%s/',
            implode(', ', $updateValues)
        );
    }

    public function testUpdateUpdatesIntoCorrectTable()
    {
        $tableName = $this->mapper->getTableName();

        $entity = $this->createEntityToUpdate();

        $this->mapper->update($entity);

        $regexp = sprintf('/UPDATE `%s` SET /', $tableName);

        $this->assertRegExpOnSqlString($regexp);
    }

    public function testUpdateUpdatesWhereIdIsEntityId()
    {
        $tableName = $this->mapper->getTableName();

        $entity = $this->createEntityToUpdate();

        $regexp = sprintf('/WHERE `id` = \'%s\'/', $entity->getId());

        $this->mapper->update($entity);

        $this->assertRegExpOnSqlString($regexp);
    }

    public function testUpdateUpdatesToCorrectValues()
    {
        $entity = $this->createEntityToUpdate();

        // $arrayCopy = $entity->getArrayCopy();
        $arrayCopy = $entity->getDbValues();

        // Query should not try to update the ID, even to the same ID
        unset($arrayCopy['id']);

        $regexp = $this->createUpdateClauseRegexp($arrayCopy);

        $this->mapper->update($entity);

        $this->assertRegExpOnSqlString($regexp);
    }

    public function testUpdateSetsUpdatedTimestampColumnAutomaticallyOnEntityAndDbQuery()
    {
        $entity = $this->createTimestampEntityToUpdate()
            ->setUpdated(null);

        $this->timestampMapper->update($entity);

        $regexp = sprintf('/\SET .+ `updated` = \'[0-9]+\' WHERE/');

        $this->assertRegExpOnSqlString($regexp);

        $this->assertNotNull($entity->getUpdated());
    }

    public function testUpdateSetsUpdatedDatetimeColumnAutomaticallyOnEntityAndDbQuery()
    {
        $entity = $this->createTimestampEntityToUpdate()
            ->setUpdated(null);

        $this->datetimeMapper->update($entity);

        $regexp = sprintf(
            '/\SET .+ `updated` = \'%s+\' WHERE/',
            '(\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2})' // datetime pattern
        );

        $this->assertRegExpOnSqlString($regexp);

        $this->assertNotNull($entity->getUpdated());
    }

    public function testUpdateOnMapperWithDifferentPrimaryKeyDoesWhereOnPrimaryKeyFields()
    {
        $keyValues = [
            'id'    => '123',
            'email' => 'foo@bar.com'
        ];
        $entity = new UserEntity($keyValues);

        $this->differentPrimaryKeyMapper->update($entity);

        $this->assertRegExpOnSqlString(
            sprintf(
                '/WHERE `id` = \'%s\' AND `email` = \'%s\'/',
                $keyValues['id'],
                $keyValues['email']
            )
        );
    }

    public function testPatchOnMapperWithNoChangesDoesNotCallExecute()
    {
        $entity = $this->createEntityToUpdate();

        $this->mapper->patch($entity);

        $this->assertEmpty($this->sqlStrings);
    }

    public function testPatchOnMapperWithOneChangeDoesNotUpdateOtherField()
    {
        $entity = $this->createEntityToUpdate();
        $entity->setEmail('x@y.com');

        $this->mapper->patch($entity);

        $this->assertEquals(
            "UPDATE `test_table` SET `email` = 'x@y.com' WHERE `id` = '1234'",
            $this->getSqlString(0)
        );
    }
}
