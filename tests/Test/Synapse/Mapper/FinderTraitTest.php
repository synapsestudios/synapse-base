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

        $this->mapper = new Mapper($this->mocks['adapter'], $this->prototype);
        $this->mapper->setSqlFactory($this->mocks['sqlFactory']);

        $this->mapper->setResultsPerPage(self::RESULTS_PER_PAGE);
    }

    public function getMockResultDataForSingleResult()
    {
        return [
            [
                'foo'    => 'a',
                'bar'    => 'b',
                'baz'    => 'c',
            ]
        ];
    }

    public function getMockResultDataForMultipleResults()
    {
        return [
            [
                'foo'    => 1,
                'bar'    => 2,
                'baz'    => 3,
            ],
            [
                'foo'    => 4,
                'bar'    => 5,
                'baz'    => 6,
            ]
        ];
    }

    public function createPrototype()
    {
        return new Entity();
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

    public function createOrderByClauseRegexp($orderOptions)
    {
        $orderQuerySections = [];

        foreach ($orderOptions as $option) {
            $column    = $option[0];
            $direction = $option[1];

            // Test that order is set to ascending if none provided
            if ($direction === null) {
                $direction = self::ORDER_ASCENDING;
            }

            $orderQuerySections[] = sprintf('`%s` %s', $column, $direction);
        }

        return sprintf(
            '/ORDER BY %s/',
            implode(', ', $orderQuerySections)
        );
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

        $regexp = $this->createWhereClauseRegexp($constraints);

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

        $regexp = $this->createWhereClauseRegexp($constraints);

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

    public function testFindBySetsOrderByColumnAscendingIfOrderColumnProvidedInOptionsArray()
    {
        $orderColumn = 'ad9fe8c7';

        $options = ['order' => [$orderColumn]];

        $constraints = $this->createSearchConstraints();

        $this->mapper->findBy($constraints, $options);

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
        $orderOptions = [
            ['ad9fe8c7', self::ORDER_ASCENDING],
            ['bd8ca9ef', self::ORDER_DESCENDING],
            ['foobarbaz', null],
        ];

        $regexp = $this->createOrderByClauseRegexp($orderOptions);

        $this->mapper->findAllBy(
            $this->createSearchConstraints(),
            ['order' => $orderOptions]
        );

        $this->assertRegExp($regexp, $this->getSqlString());
    }

    public function testFindAllByFormsExplicitEqualWhereClausesCorrectly()
    {
        $constraints = [
            ['foo', '=', 'barbazqux']
        ];

        $this->mapper->findAllBy($constraints);

        $this->assertRegExpOnSqlString('/WHERE `foo` = \'barbazqux\'/');
    }

    public function testFindAllByFormsNotEqualWhereClausesCorrectly()
    {
        $constraints = [
            ['foo', '!=', 'bar']
        ];

        $this->mapper->findAllBy($constraints);

        $this->assertRegExpOnSqlString('/WHERE `foo` != \'bar\'/');
    }

    public function testFindAllByFormsGreaterThanWhereClausesCorrectly()
    {
        $constraints = [
            ['foo', '>', 'bar']
        ];

        $this->mapper->findAllBy($constraints);

        $this->assertRegExpOnSqlString('/WHERE `foo` > \'bar\'/');
    }

    public function testFindAllByFormsLessThanWhereClausesCorrectly()
    {
        $constraints = [
            ['foo', '<', 'bar']
        ];

        $this->mapper->findAllBy($constraints);

        $this->assertRegExpOnSqlString('/WHERE `foo` < \'bar\'/');
    }

    public function testFindAllByFormsGreaterThanOrEqualToWhereClausesCorrectly()
    {
        $constraints = [
            ['foo', '>=', 'bar']
        ];

        $this->mapper->findAllBy($constraints);

        $this->assertRegExpOnSqlString('/WHERE `foo` >= \'bar\'/');
    }

    public function testFindAllByFormsLessThanOrEqualToWhereClausesCorrectly()
    {
        $constraints = [
            ['foo', '<=', 'bar']
        ];

        $this->mapper->findAllBy($constraints);

        $this->assertRegExpOnSqlString('/WHERE `foo` <= \'bar\'/');
    }

    public function testFindAllByFormsLikeWhereClausesCorrectly()
    {
        $constraints = [
            ['foo', 'LIKE', 'bar']
        ];

        $this->mapper->findAllBy($constraints);

        $this->assertRegExpOnSqlString('/WHERE `foo` LIKE \'bar\'/');
    }

    public function testFindAllByFormsNotLikeWhereClausesCorrectly()
    {
        $constraints = [
            ['foo', 'NOT LIKE', 'bar']
        ];

        $this->mapper->findAllBy($constraints);

        $this->assertRegExpOnSqlString('/WHERE `foo` NOT LIKE \'bar\'/');
    }

    public function testFindAllByFormsInWhereClausesCorrectly()
    {
        $inArray = ['bar', 'baz', 'qux'];

        $constraints = [
            ['foo', 'IN', $inArray]
        ];

        $this->mapper->findAllBy($constraints);

        $regexp = sprintf(
            '/WHERE `foo` IN \(\'%s\'\)/',
            implode('\', \'', $inArray)
        );

        $this->assertRegExpOnSqlString($regexp);
    }

    public function testFindAllByFormsNotInWhereClausesCorrectly()
    {
        $inArray = ['bar', 'baz', 'qux'];

        $this->mapper->findAllBy([
            ['foo', 'NOT IN', $inArray]
        ]);

        $this->assertRegExpOnSqlString('/WHERE `foo` NOT IN \(\'bar\', \'baz\', \'qux\'\)/');
    }

    public function testFindAllByFormsIsNullClausesCorrectly()
    {
        $this->mapper->findAllBy([
            ['foo', 'IS', 'NULL']
        ]);

        $regexp = '/WHERE `foo` IS NULL/';

        $this->assertRegExpOnSqlString($regexp);

        $this->mapper->findAllBy([
            ['foo', 'IS', null]
        ]);

        $this->assertRegExpOnSqlString($regexp, 1);
    }

    public function testFindAllByFormsIsNotNullClausesCorrectly()
    {
        $this->mapper->findAllBy([
            ['foo', 'IS NOT', 'NULL']
        ]);

        $regexp = '/WHERE `foo` IS NOT NULL/';

        $this->assertRegExpOnSqlString($regexp);

        $this->mapper->findAllBy([
            ['foo', 'IS NOT', null]
        ]);

        $this->assertRegExpOnSqlString($regexp, 1);
    }

    public function testFindAllByFormsInClausesWith3ItemsCorrectly()
    {
        $this->mapper->findAllBy([
            'foo' => [1, 2, 3]
        ]);

        $this->assertRegExpOnSqlString('/WHERE `foo` IN \(\'1\', \'2\', \'3\'\)/');
    }

    /**
     * @expectedException LogicException
     */
    public function testFindAllByThrowsExceptionIfPageGivenButOrderNotGiven()
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

    public function testFindByReturnsEntityOfResultData()
    {
        $mockResults = $this->getMockResultDataForSingleResult();
        $this->setMockResults($mockResults);

        $result = $this->mapper->findBy([]);

        $this->assertInstanceOf('Synapse\Entity\AbstractEntity', $result);

        $this->assertEquals(
            $mockResults,
            [$result->getArrayCopy()]
        );
    }

    public function testFindAllReturnsEntityIteratorOfResults()
    {
        $mockResults = $this->getMockResultDataForMultipleResults();
        $this->setMockResults($mockResults);

        $result = $this->mapper->findAll();

        $this->assertInstanceOf('Synapse\Entity\EntityIterator', $result);

        $this->assertEquals(
            $mockResults,
            $result->getArrayCopy()
        );
    }

    // This is more of a test of MapperTestCase, but it isn't all that out of place here
    public function testSetMockResultsCanMockMultipleCalls()
    {
        $firstMockResults  = $this->getMockResultDataForSingleResult();
        $secondMockResults = $this->getMockResultDataForMultipleResults();
        $this->setMockResults($firstMockResults, $secondMockResults);

        $firstResult = $this->mapper->findBy([]);
        $secondResult = $this->mapper->findAll();

        $this->assertEquals(
            $firstMockResults,
            [$firstResult->getArrayCopy()]
        );
        $this->assertEquals(
            $secondMockResults,
            $secondResult->getArrayCopy()
        );
    }
}
