<?php

namespace Test\Synapse\Mapper;

use Synapse\TestHelper\MapperTestCase;

class PivotInserterTraitTest extends MapperTestCase
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

    public function createEntityToInsert()
    {
        return new PivotEntity([
            'foo_id' => 10,
            'bar_id' => 15,
        ]);
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

        $regexp = sprintf('/%s VALUES %s/', $columns, $values);

        $this->mapper->insert($entity);

        $this->assertRegExp($regexp, $this->getSqlString());
    }
}
