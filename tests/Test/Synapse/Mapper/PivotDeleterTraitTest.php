<?php

namespace Test\Synapse\Mapper;

use Synapse\TestHelper\MapperTestCase;

class PivotDeleterTraitTest extends MapperTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->prototype = $this->createPrototype();

        $this->mapper = new PivotMapper($this->mockAdapter, $this->prototype);
        $this->mapper->setSqlFactory($this->mockSqlFactory);
    }

    public function createPrototype()
    {
        return new PivotEntity();
    }

    public function createEntityToDelete()
    {
        return new PivotEntity([
            'foo_id' => 10,
            'bar_id' => 15,
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

    public function testDeleteDeletesFromCorrectTable()
    {
        $tableName = $this->mapper->getTableName();

        $entity = $this->createEntityToDelete();

        $this->mapper->delete($entity);

        $regexp = sprintf('/DELETE FROM `%s`/', $tableName);

        $this->assertRegExp($regexp, $this->getSqlString());
    }

    public function testDeleteAddsWhereClausesForEachColumn()
    {
        $entity = $this->createEntityToDelete();

        $whereValues = [];

        foreach ($entity->getArrayCopy() as $field => $value) {
            if ($value === null) {
                $whereValue = sprintf('`%s` IS NULL', $field);
            } else {
                $whereValue = sprintf('`%s` = \'%s\'', $field, $value);
            }

            $whereValues[] = $whereValue;
        }

        $whereValueString = implode(' AND ', $whereValues);

        $regexp = sprintf('/WHERE %s/', $whereValueString);

        $this->mapper->delete($entity);

        $this->assertRegExp($regexp, $this->getSqlString());
    }
}
