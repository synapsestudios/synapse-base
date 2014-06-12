<?php

namespace Test\Synapse\Mapper;

use Synapse\TestHelper\MapperTestCase;
use Synapse\User\UserEntity;
use stdClass;

class FinderTraitTest extends MapperTestCase
{
    const ORDER_ASCENDING  = 'ASC';
    const ORDER_DESCENDING = 'DESC';

    // Set to a non-round number to make the tests stronger
    const RESULTS_PER_PAGE = 47;

    public function setUp()
    {
        parent::setUp();

        $this->captured = new stdClass();

        $this->prototype = $this->createPrototype();

        $this->mapper = new Mapper($this->mockAdapter, $this->prototype);
        $this->mapper->setSqlFactory($this->mockSqlFactory);

        $this->mapper->setResultsPerPage(self::RESULTS_PER_PAGE);
    }

    public function createPrototype()
    {
        return new UserEntity();
    }

    public function createUserEntity()
    {
        return new UserEntity([
            'id'       => 100,
            'email'    => 'address@a.com',
            'password' => 'password',
        ]);
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

    public function withUserEntityFound()
    {
        $userEntity = $this->createUserEntity();

        $this->mockResult->expects($this->any())
            ->method('current')
            ->will($this->returnCallback(function () use ($userEntity) {
                $this->captured->foundUserEntityData = $userEntity->getArrayCopy();

                return $this->captured->foundUserEntityData;
            }));
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

    public function testFindByReturnsEntityWithFoundDataInjected()
    {
        $this->withUserEntityFound();

        $result = $this->mapper->findBy([]);

        $this->assertSame(
            $this->captured->foundUserEntityData,
            $result->getArrayCopy()
        );
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

    /**
     * @expectedException LogicException
     */
    public function testFindAllByThrowExceptionIfPageGivenButOrderNotGiven()
    {
        $options = ['page'  => 6];

        $this->mapper->findAllBy($this->createSearchConstraints(), $options);
    }

    public function testFindAllBySetsLimitAndOffsetIfPaginationOptionsProvided()
    {
        $page  = 6;
        $order = 'foo';

        $options = [
            'page'  => $page,
            'order' => [$order],
        ];

        $this->mapper->findAllBy($this->createSearchConstraints(), $options);

        // Results per page set in $this::setUp
        $limit  = self::RESULTS_PER_PAGE;

        // Offset is the start of the page
        $offset = ($page - 1) * $limit;

        $regexp = sprintf(
            '/LIMIT \'%s\' OFFSET \'%s\'/',
            $limit,
            $offset
        );

        $this->assertRegExpOnSqlString($regexp);
    }

    public function testFindAllBySetsPageTo1IfBelow1()
    {
        $page  = -15;
        $order = 'foo';

        $options = [
            'page'  => $page,
            'order' => [$order],
        ];

        $this->mapper->findAllBy($this->createSearchConstraints(), $options);

        // Results per page set in $this::setUp
        $limit  = self::RESULTS_PER_PAGE;

        // Offset is the start of the page
        $offset = ($page - 1) * $limit;

        $regexp = sprintf(
            '/LIMIT \'%s\' OFFSET \'%s\'/',
            $limit,
            $offset
        );

        $this->assertRegExpOnSqlString($regexp);
    }

    public function testFindAllSimplySelectsAllRowsFromTable()
    {
        $tableName = $this->mapper->getTableName();

        $this->mapper->findAll();

        $regexp = sprintf('/^SELECT `%s`.* FROM `%s`$/', $tableName, $tableName);

        $this->assertRegExp($regexp, $this->getSqlString());
    }
}
