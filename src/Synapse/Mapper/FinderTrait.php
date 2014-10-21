<?php

namespace Synapse\Mapper;

use InvalidArgumentException;
use LogicException;
use Synapse\Mapper\PaginationData;
use Synapse\Stdlib\Arr;
use Synapse\Entity\EntityIterator;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\PreparableSqlInterface;
use Zend\Db\Sql\Predicate\Like;
use Zend\Db\Sql\Predicate\NotLike;
use Zend\Db\Sql\Predicate\Operator;
use Zend\Db\Sql\Predicate\In;

/**
 * Use this trait to add find functionality to AbstractMappers.
 */
trait FinderTrait
{
    /**
     * Default maximum number of results to return if pagination is used
     *
     * @var integer
     */
    protected $resultsPerPage = 50;

    /**
     * Set maximum number of results to return if pagination is enabled
     *
     * @param int $resultsPerPage
     */
    public function setResultsPerPage($resultsPerPage)
    {
        $this->resultsPerPage = $resultsPerPage;
    }

    /**
     * Find a single entity by specific field values
     *
     * @param  array  $wheres An array of where conditions in the format:
     *                        ['column' => 'value'] or
     *                        ['column', 'operator', 'value']
     * @param  array  $options
     * @return AbstractEntity|bool
     */
    public function findBy(array $wheres, array $options = [])
    {
        $query = $this->getSqlObject()->select();

        $this->setColumns($query, $options);

        $wheres = $this->addJoins($query, $wheres, $options);

        $this->addWheres($query, $wheres, $options);

        return $this->executeAndGetResultsAsEntity($query);
    }

    /**
     * Find a single entity by ID
     *
     * @param  int|string $id Entity ID
     * @return AbstractEntity|bool
     */
    public function findById($id)
    {
        return $this->findBy(['id' => $id]);
    }

    /**
     * Find all entities matching specific field values
     *
     * @param  array $wheres  An array of where conditions in the format:
     *                        ['column' => 'value'] or
     *                        ['column', 'operator', 'value']
     * @param  array $options Array of options for this request.
     *                        May include 'order', 'page', or 'resultsPerPage'.
     * @return array          Array of AbstractEntity objects
     * @throws Exception      If pagination enabled and no 'order' option specified.
     */
    public function findAllBy(array $wheres, array $options = [])
    {
        $query = $this->getSqlObject()->select();

        $this->setColumns($query, $options);

        $wheres = $this->addJoins($query, $wheres, $options);

        $this->addWheres($query, $wheres, $options);

        $page = Arr::get($options, 'page');

        if ($page && !Arr::get($options, 'order')) {
            throw new LogicException('Must provide an ORDER BY if using pagination');
        }

        if (Arr::get($options, 'order')) {
            $this->setOrder($query, $options['order']);
        }

        if ($page) {
            $paginationData = $this->getPaginationData($query, $options);
            // Set LIMIT and OFFSET
            $query->limit($paginationData->getResultsPerPage());
            $query->offset(($page - 1) * $paginationData->getResultsPerPage());
        }

        $entityIterator = $this->executeAndGetResultsAsEntityIterator($query);

        if ($page) {
            $entityIterator->setPaginationData($paginationData);
        }

        return $entityIterator;
    }

    /**
     * Find all entities in this table
     *
     * @param  array $options Array of options for this request
     * @return array          Array of AbstractEntity objects
     */
    public function findAll(array $options = [])
    {
        return $this->findAllBy([], $options);
    }

    /**
     * Set the order on the given query
     *
     * Can specify order as [['column', 'direction'], ['column', 'direction']]
     * or just ['column', 'direction'] or even [['column', 'direction'], 'column']
     *
     * @param Select $query
     * @param array  $order
     */
    protected function setOrder(Select $query, array $order)
    {
        // Normalize to [['column', 'direction']] format if only one column
        if (! is_array(Arr::get($order, 0))) {
            $order = [$order];
        }

        foreach ($order as $key => $orderValue) {
            if (is_array($orderValue)) {
                // Column and direction
                $query->order(
                    Arr::get($orderValue, 0).' '.Arr::get($orderValue, 1)
                );
            }
        }

        return $query;
    }

    /**
     * Get data object with pagination data like page and page_count
     *
     * @param  Select $query
     * @param  array $options
     * @return PaginationData
     */
    protected function getPaginationData(Select $query, array $options)
    {
        // Get pagination options
        $page = (int) Arr::get($options, 'page');

        if ($page < 1) {
            $page = 1;
        }

        $resultsPerPage = Arr::get(
            $options,
            'resultsPerPage',
            $this->resultsPerPage
        );

        // Get total results
        $resultCount = $this->getQueryResultCount($query);
        $pageCount   = ceil($resultCount / $resultsPerPage);

        return new PaginationData([
            'page'             => $page,
            'page_count'       => $pageCount,
            'result_count'     => $resultCount,
            'results_per_page' => $resultsPerPage
        ]);
    }

    /**
     * Get the count of results from a given query
     *
     * @param  Select $query
     * @return int
     */
    protected function getQueryResultCount(Select $query)
    {
        $queryString = $this->getSqlObject()->getSqlStringForSqlObject($query, $this->dbAdapter->getPlatform());

        $format = 'Select count(*) as `count` from (%s) as `query_count`';

        $countQueryString = sprintf($format, $queryString);

        $countQuery = $this->dbAdapter->query($countQueryString);

        $result = $countQuery->execute()->current();

        return (int) Arr::get($result, 'count');
    }

    /**
     * Add where clauses to query
     *
     * @param PreparableSqlInterface $query
     * @param array              $wheres An array of where conditions in the format:
     *                                   ['column' => 'value'] or
     *                                   ['column', 'operator', 'value']
     * @param  array             $options
     * @return PreparableSqlInterface
     * @throws InvalidArgumentException  If a WHERE requirement is in an unsupported format.
     */
    protected function addWheres(PreparableSqlInterface $query, array $wheres, array $options = [])
    {
        foreach ($wheres as $key => $where) {
            if (is_array($where) && count($where) === 3) {
                $operator = $where[1];

                switch ($operator)
                {
                    case '=':
                        $predicate = new Operator(
                            $where[0],
                            Operator::OP_EQ,
                            $where[2]
                        );
                        break;
                    case '!=':
                        $predicate = new Operator(
                            $where[0],
                            Operator::OP_NE,
                            $where[2]
                        );
                        break;
                    case '>':
                        $predicate = new Operator(
                            $where[0],
                            Operator::OP_GT,
                            $where[2]
                        );
                        break;
                    case '<':
                        $predicate = new Operator(
                            $where[0],
                            Operator::OP_LT,
                            $where[2]
                        );
                        break;
                    case '>=':
                        $predicate = new Operator(
                            $where[0],
                            Operator::OP_GTE,
                            $where[2]
                        );
                        break;
                    case '<=':
                        $predicate = new Operator(
                            $where[0],
                            Operator::OP_LTE,
                            $where[2]
                        );
                        break;
                    case 'LIKE':
                        $predicate = new Like($where[0], $where[2]);
                        break;
                    case 'NOT LIKE':
                        $predicate = new NotLike($where[0], $where[2]);
                        break;
                    case 'IN':
                        $predicate = new In($where[0], $where[2]);
                        break;
                }

                $query->where($predicate);
            } else {
                $query->where([$key => $where]);
            }
        }

        return $query;
    }

    /**
     * Add any necessary joins to the query.
     *
     * @param  PreparableSqlInterface $query
     * @param  array                  $wheres
     * @param  array                  $options
     * @return array                          Updated $wheres that may include
     *                                        fully qualified names.
     */
    protected function addJoins($query, $wheres, $options = [])
    {
        // Override if joins are required
        return $wheres;
    }

    /**
     * Set the columns to be selected
     *
     * By default selects all of the columns for the table
     * associated with the mapper
     *
     * @param PreparableSqlInterface $query
     * @param array                  $options
     */
    protected function setColumns($query, $options = [])
    {
        $query->columns(['*']);
    }
}
