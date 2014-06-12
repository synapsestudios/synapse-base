<?php

namespace Test\Synapse\Mapper;

use Synapse\TestHelper\MapperTestCase;
use Synapse\User\UserEntity;

class FinderTraitTest extends MapperTestCase
{
    const ORDER_ASCENDING  = 'ASC';
    const ORDER_DESCENDING = 'DESC';

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

    public function createSearchConstraints()
    {
        return [
            'foo'  => 'bar',
            'baz'  => 1.99,
            'qux'  => null,
            'life' => 42,
        ];
    }

    public function provideOrderDirectionValues()
    {
        return [
            ['ASC'],
            ['DESC'],
        ];
    }

    public function testFindBySelectsAllColumnsFromTableByDefault()
    {
        $tableName = $this->mapper->getTableName();

        $constraints = $this->createSearchConstraints();

        $this->mapper->findBy($constraints);

        $regexp = sprintf('/SELECT `%s`.* FROM `%s`/', $tableName, $tableName);

        $this->assertRegExp($regexp, $this->getSqlString());
    }

    public function testFindByConstructsWhereClausesWithAnds()
    {
        $tableName = $this->mapper->getTableName();

        $constraints = $this->createSearchConstraints();

        $this->mapper->findBy($constraints);

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

        $regexp = sprintf('/WHERE %s/', $whereValueString);

        $this->assertRegExp($regexp, $this->getSqlString());
    }

    public function testFindByIdSelectsAllColumnsFromTableWhereIdIsNumberProvided()
    {
        $id = 112358;

        $tableName = $this->mapper->getTableName();

        $this->mapper->findById($id);

        $regexp = sprintf('/SELECT `%s`.* FROM `%s` WHERE `id` = \'%s\'/', $tableName, $tableName, $id);

        $this->assertRegExp($regexp, $this->getSqlString());
    }

    public function testFindAllBySelectsAllColumnsFromTableByDefault()
    {
        $tableName = $this->mapper->getTableName();

        $constraints = $this->createSearchConstraints();

        $this->mapper->findAllBy($constraints);

        $regexp = sprintf('/SELECT `%s`.* FROM `%s`/', $tableName, $tableName);

        $this->assertRegExp($regexp, $this->getSqlString());
    }

    public function testFindAllByConstructsWhereClausesWithAnds()
    {
        $tableName = $this->mapper->getTableName();

        $constraints = $this->createSearchConstraints();

        $this->mapper->findAllBy($constraints);

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

        $regexp = sprintf('/WHERE %s/', $whereValueString);

        $this->assertRegExp($regexp, $this->getSqlString());
    }

    public function testFindAllBySetsOrderByColumnAscendingIfOrderColumnProvidedInOptionsArray()
    {
        $orderColumn = 'ad9fe8c7';

        $options = ['order' => [$orderColumn]];

        $constraints = $this->createSearchConstraints();

        $this->mapper->findAllBy($constraints, $options);

        $regexp = sprintf('/ORDER BY `%s` ASC/', $orderColumn);

        $this->assertRegExp($regexp, $this->getSqlString());
    }

    /**
     * @dataProvider provideOrderDirectionValues
     */
    public function testFindAllBySetsOrderByWithGivenDirectionIfProvidedInOptionsArray($orderDirection)
    {
        $orderColumn = 'ad9fe8c7';

        $options = ['order' => [$orderColumn, $orderDirection]];

        $constraints = $this->createSearchConstraints();

        $this->mapper->findAllBy($constraints, $options);

        $regexp = sprintf('/ORDER BY `%s` %s/', $orderColumn, $orderDirection);

        $this->assertRegExp($regexp, $this->getSqlString());
    }

    public function testFindAllBySetsMultipleOrdersIfProvidedAndSetsDirectionToAscendingIfNoneProvided()
    {
        $orderColumnsAndDirections = [
            'ad9fe8c7'  => self::ORDER_ASCENDING,
            'bd8ca9ef'  => self::ORDER_DESCENDING,
            'foobarbaz' => null,
        ];

        $orderOptions = [];
        $orderQuerySections = [];

        foreach ($orderColumnsAndDirections as $column => $direction) {
            $orderOptions[] = [$column, $direction];

            // Test that order is set to ascending if none provided
            if ($direction === null) {
                $direction = self::ORDER_ASCENDING;
            }

            $orderQuerySections[] = sprintf('`%s` %s', $column, $direction);
        }

        $options = ['order' => $orderOptions];

        $this->mapper->findAllBy($this->createSearchConstraints(), $options);

        $regexp = sprintf(
            '/ORDER BY %s/',
            implode(', ', $orderQuerySections)
        );

        $this->assertRegExp($regexp, $this->getSqlString());
    }

    public function testFindAllBySimplySelectsAllRowsFromTable()
    {
        $tableName = $this->mapper->getTableName();

        $this->mapper->findAll();

        $regexp = sprintf('/^SELECT `%s`.* FROM `%s`$/', $tableName, $tableName);

        $this->assertRegExp($regexp, $this->getSqlString());
    }
}
