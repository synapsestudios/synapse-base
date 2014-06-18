<?php

namespace Synapse\TestHelper;

use PHPUnit_Framework_TestCase;
use Synapse\Stdlib\Arr;
use Zend\Db\Adapter\Platform\Mysql as MysqlPlatform;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\SqlInterface;
use Zend\Db\Sql\Update;

/**
 * Class for testing mappers.  Currently expects that you are using Mysqli.
 *
 * To use:
 * 1. Call parent::setUp() from setUp
 * 2. Instantiate the mapper
 * 3. Call setSqlFactory($this->mockSqlFactory) on the mapper.
 * 4. In your tests, get query strings with $this->getSqlStrings().
 */
abstract class MapperTestCase extends PHPUnit_Framework_TestCase
{
    const GENERATED_ID = 123;

    protected $sqlStrings = [];

    protected $queries = [];

    protected $fallbackTableName = 'table';

    public function setUp()
    {
        $this->sqlStrings = [];

        $this->setUpMockAdapter();

        $this->setUpMockSqlFactory();
    }

    public function getPlatform()
    {
        $mockMysqli = $this->getMockBuilder('mysqli')
            ->disableOriginalConstructor()
            ->getMock();

        $mockMysqli->expects($this->any())
            ->method('real_escape_string')
            ->will($this->returnCallback(function ($value) {
                return addslashes($value);
            }));

        return new MysqlPlatform($mockMysqli);
    }

    public function getQueryAsSqlString(SqlInterface $query)
    {
        return $query->getSqlString($this->getPlatform());
    }

    public function getMockResult()
    {
        $mockResult = $this->getMock('Zend\Db\Adapter\Driver\ResultInterface');

        $mockResult->expects($this->any())
            ->method('getGeneratedValue')
            ->will($this->returnValue(self::GENERATED_ID));

        return $mockResult;
    }

    public function getMockStatement()
    {
        $mockStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');

        $mockStatement->expects($this->any())
            ->method('execute')
            ->will($this->returnValue($this->getMockResult()));

        return $mockStatement;
    }

    public function setUpMockAdapter()
    {
        $this->mockAdapter = $this->getMockBuilder('Zend\Db\Adapter\Adapter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockAdapter->expects($this->any())
            ->method('query')
            ->will($this->returnCallback(function ($sql, $mode) {
                $this->sqlStrings[] = $sql;

                if ($mode === 'prepare') {
                    return $this->getMockStatement();
                } else {
                    return $this->getMockResult();
                }
            }));

        $this->mockDriver = $this->getMockBuilder('Zend\Db\Adapter\Driver\Mysqli\Mysqli')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockDriver->expects($this->any())
            ->method('createStatement')
            ->will($this->returnValue($this->getMockStatement()));

        $this->mockConnection = $this->getMock('Zend\Db\Adapter\Driver\ConnectionInterface');

        $this->mockDriver->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->mockConnection));

        $this->mockConnection->expects($this->any())
            ->method('getResource')
            ->will($this->returnValue(
                $this->getMockBuilder('mysqli')
                    ->disableOriginalConstructor()
                    ->getMock()
            ));

        $this->mockAdapter->expects($this->any())
            ->method('getDriver')
            ->will($this->returnValue($this->mockDriver));

        $this->mockAdapter->expects($this->any())
            ->method('getPlatform')
            ->will($this->returnValue($this->getPlatform()));
    }

    public function getMockSql()
    {
        $mockSql = $this->getMockBuilder('Zend\Db\Sql\Sql')
            ->setMethods(['select', 'insert', 'update', 'delete', 'prepareStatementForSqlObject'])
            ->disableOriginalConstructor()
            ->getMock();

        $mockSql->expects($this->any())
            ->method('prepareStatementForSqlObject')
            ->will($this->returnValue($this->getMockStatement()));

        $mockSql->expects($this->any())
            ->method('select')
            ->will($this->returnCallback(function () use ($mockSql) {
                $table = $mockSql->getTable() ?: (
                    $this->mapper ?
                    $this->mapper->getTableName() :
                    $this->fallbackTableName
                );
                $select = new Select($table);

                $this->queries[] = $select;

                return $select;
            }));

        $mockSql->expects($this->any())
            ->method('insert')
            ->will($this->returnCallback(function () use ($mockSql) {
                $table = $mockSql->getTable() ?: (
                    $this->mapper ?
                    $this->mapper->getTableName() :
                    $this->fallbackTableName
                );
                $insert = new Insert($table);

                $this->queries[] = $insert;

                return $insert;
            }));

        $mockSql->expects($this->any())
            ->method('update')
            ->will($this->returnCallback(function () use ($mockSql) {
                $table = $mockSql->getTable() ?: (
                    $this->mapper ?
                    $this->mapper->getTableName() :
                    $this->fallbackTableName
                );
                $update = new Update($table);

                $this->queries[] = $update;

                return $update;
            }));

        $mockSql->expects($this->any())
            ->method('delete')
            ->will($this->returnCallback(function () use ($mockSql) {
                $table = $mockSql->getTable() ?: (
                    $this->mapper ?
                    $this->mapper->getTableName() :
                    $this->fallbackTableName
                );
                $delete = new Delete($table);

                $this->queries[] = $delete;

                return $delete;
            }));

        return $mockSql;
    }

    public function setUpMockSqlFactory()
    {
        $this->mockSqlFactory = $this->getMock('Synapse\Mapper\SqlFactory');

        $this->mockSqlFactory->expects($this->any())
            ->method('getSqlObject')
            // Using returnCallback, because otherwise a reference to the same object will
            // be returned every time.
            ->will($this->returnCallback(function () {
                return $this->getMockSql();
            }));
    }

    protected function getSqlStrings()
    {
        $stringifiedQueries = array_map(function ($query) {
            return $this->getQueryAsSqlString($query);
        }, $this->queries);

        return array_merge($stringifiedQueries, $this->sqlStrings);
    }

    protected function getSqlString($key = 0)
    {
        $sqlStrings = $this->getSqlStrings();

        return Arr::get($sqlStrings, $key);
    }

    protected function assertRegExpOnSqlString($regexp, $sqlStringKey = 0)
    {
        $sqlString = $this->getSqlString($sqlStringKey);

        $this->assertRegExp($regexp, $sqlString);
    }
}
