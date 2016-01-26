<?php

namespace Synapse\TestHelper;

use stdClass;
use Synapse\Stdlib\Arr;
use Zend\Db\Adapter\Platform\Mysql as MysqlPlatform;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\SqlInterface;

/**
 * Class for testing mappers.  Currently expects that you are using Mysqli.
 *
 * To use:
 * 1. Call parent::setUp() from setUp
 * 2. Instantiate the mapper
 * 3. Call setSqlFactory($this->mocks['sqlFactory']) on the mapper.
 * 4. In your tests, get query strings with $this->getSqlStrings().
 */
abstract class MapperTestCase extends TestCase
{
    const GENERATED_ID = 123;

    /**
     * SQL strings of queries that have been run
     *
     * @var array
     */
    protected $sqlStrings = [];

    /**
     * List of query parameters passed in with queries
     *
     * Keys correspond to $this->sqlStrings
     *
     * @var array
     */
    protected $queryParameters = [];

    /**
     * Query objects run
     *
     * @var array(Zend\Db\Sql\AbstractSql)
     */
    protected $queries = [];

    /**
     * Set using the setMockResults method
     *
     * @var array
     */
    protected $mockResults = [];

    protected $fallbackTableName = 'table';
    protected $mockResultCount;
    protected $mapper;

    public function setUp()
    {
        $this->sqlStrings = [];

        $this->mockResultCount = 0;

        $this->mockResults = [];

        $this->setUpMockAdapter();

        $this->setUpMockSqlFactory();
    }

    /**
     * Set array of data that a query result will contain
     *
     * @internal param array $results ... Array of data for result to contain.  Repeatable
     *                          if multiple queries will be executed.
     */
    public function setMockResults()
    {
        $results = [];
        $resultArrays = func_get_args();

        foreach ($resultArrays as $resultArray) {
            $results[] = new MockQueryResult($resultArray);
        }

        $this->mockResults = $results;
    }

    public function getPlatform()
    {
        $mockMysqli = $this->getMock('mysqli');

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
        if (! isset($this->mockResults[$this->mockResultCount])) {
            // return empty results
            $result = new MockQueryResult([]);
            $result->setGeneratedValue(self::GENERATED_ID);
            return $result;
        }

        $result = $this->mockResults[$this->mockResultCount];

        $this->mockResultCount += 1;

        return $result;
    }

    public function getMockStatement()
    {
        $mockStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');

        $mockStatement->expects($this->any())
            ->method('execute')
            ->will($this->returnCallback(function () {
                return $this->getMockResult();
            }));

        return $mockStatement;
    }

    public function setUpMockAdapter()
    {
        $this->mocks['adapter'] = $this->getMockBuilder('Zend\Db\Adapter\Adapter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mocks['adapter']->expects($this->any())
            ->method('query')
            ->will($this->returnCallback([$this, 'handleAdapterQuery']));

        $this->mocks['driver'] = $this->getMockBuilder('Zend\Db\Adapter\Driver\Mysqli\Mysqli')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mocks['driver']->expects($this->any())
            ->method('createStatement')
            ->will($this->returnValue($this->getMockStatement()));

        $this->mocks['connection'] = $this->getMock('Zend\Db\Adapter\Driver\ConnectionInterface');

        $this->mocks['driver']->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->mocks['connection']));

        $this->mocks['connection']->expects($this->any())
            ->method('getResource')
            ->will($this->returnValue(
                $this->getMock('mysqli')
            ));

        $this->mocks['adapter']->expects($this->any())
            ->method('getDriver')
            ->will($this->returnValue($this->mocks['driver']));

        $this->mocks['adapter']->expects($this->any())
            ->method('getPlatform')
            ->will($this->returnValue($this->getPlatform()));
    }

    /**
     * Mock a call to Zend\Db\Adapter\Adapter::query
     *
     * Captures the SQL string and parameters (if any were passed)
     *
     * @param  string       $sqlString             The SQL query in string format
     * @param  array|string $parametersOrQueryMode Just like the method being mocked, this can be either
     * @return mixed        If query mode is execute, mock results are returned; otherwise a mock statement
     */
    public function handleAdapterQuery($sqlString, $parametersOrQueryMode)
    {
        $parameters              = is_array($parametersOrQueryMode) ? $parametersOrQueryMode : [];
        $executeMode             = $parametersOrQueryMode === Adapter::QUERY_MODE_EXECUTE;
        $this->sqlStrings[]      = $sqlString;
        $this->queryParameters[] = $parameters;

        $response = $executeMode ? $this->getMockResult() : $this->getMockStatement();

        return $response;
    }

    public function getMockSql($table = null)
    {
        $mockSql = $this->getMockBuilder('Zend\Db\Sql\Sql')
            ->setMethods(['select', 'insert', 'update', 'delete', 'prepareStatementForSqlObject'])
            ->disableOriginalConstructor()
            ->getMock();

        $mockSql->expects($this->any())
            ->method('prepareStatementForSqlObject')
            ->will($this->returnValue($this->getMockStatement()));

        $defaultTable = $this->mapper ? $this->mapper->getTableName() : $this->fallbackTableName;
        $table        = $table ?: $defaultTable;

        foreach (['select', 'insert', 'update', 'delete'] as $method) {
            $mockSql->expects($this->any())
                ->method($method)
                ->will($this->returnCallback(function () use ($mockSql, $table, $method) {
                    $class = '\Zend\Db\Sql\\'.ucfirst($method);
                    $query = new $class($table);

                    $this->queries[] = $query;

                    return $query;
                }));
        }

        return $mockSql;
    }

    public function setUpMockSqlFactory()
    {
        $this->mocks['sqlFactory'] = $this->getMock('Synapse\Mapper\SqlFactory');

        $this->mocks['sqlFactory']->expects($this->any())
            ->method('getSqlObject')
            // Using returnCallback, because otherwise a reference to the same object will
            // be returned every time.
            ->will($this->returnCallback(function ($adapter, $table = null) {
                return $this->getMockSql($table);
            }));
    }

    /**
     * Get SQL strings for all queries run
     *
     * @return array(string)
     */
    protected function getSqlStrings()
    {
        $stringifiedQueries = array_map(function ($query) {
            return $this->getQueryAsSqlString($query);
        }, $this->queries);

        return array_merge($stringifiedQueries, $this->sqlStrings);
    }

    /**
     * Get the SQL string at the given zero-based index
     * (0 = the first query run, 1 = the second, etc)
     *
     * @param  integer $key Key of the SQL query
     * @return string|null  Null if none found
     */
    protected function getSqlString($key = 0)
    {
        $sqlStrings = $this->getSqlStrings();

        return Arr::get($sqlStrings, $key);
    }

    /**
     * Get the query parameters given for the SQL query provided
     *
     * @param  integer $key Key of the SQL query run (0 = the first query run, 1 = the second, etc)
     * @return array|null   List of parameters or null if none found
     */
    protected function getQueryParams($key = 0)
    {
        return Arr::get($this->queryParameters, $key);
    }

    /**
     * Assert that the SQL string at the given key matches the provided regular expression
     *
     * @param  string  $regexp       The regular expression to match against
     * @param  integer $sqlStringKey Key of the SQL query for which to make the assertion
     */
    protected function assertRegExpOnSqlString($regexp, $sqlStringKey = 0)
    {
        $sqlString = $this->getSqlString($sqlStringKey);

        $this->assertRegExp($regexp, $sqlString);
    }
}
