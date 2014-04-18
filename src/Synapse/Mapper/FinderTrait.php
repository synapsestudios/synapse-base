<?php

namespace Synapse\Mapper;

use InvalidArgumentException;
use Synapse\Stdlib\Arr;
use Synapse\Entity\EntityIterator;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Like;
use Zend\Db\Sql\Predicate\NotLike;
use Zend\Db\Sql\Predicate\Operator;

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
     * @return AbstractEntity|bool
     */
    public function findBy(array $wheres)
    {
        $query = $this->sql()->select();

        $wheres = $this->addJoins($query, $wheres);

        $this->addWheres($query, $wheres);

        $data = $this->execute($query)->current();

        if (! $data || count($data) === 0) {
            return false;
        }

        return $data;
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
        $query = $this->sql()->select();

        $wheres = $this->addJoins($query, $wheres);

        $this->addWheres($query, $wheres);

        $page = Arr::get($options, 'page');

        if ($page && !Arr::get($options, 'order')) {
            throw new Exception('Must provide an ORDER BY if using pagination');
        }

        $this->setOrder($query, $options);

        if ($page) {
            $paginationData = $this->getPaginationData($query, $options);
            // Set LIMIT and OFFSET
            $query->limit($paginationData->getResultsPerPage());
            $query->offset(($page - 1) * $paginationData->getResultsPerPage());
        }

        $entities = $this->execute($query)
            ->toEntityArray();

        $entityIterator = new EntityIterator($entities);

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
     * @param array  $options Array of options which may or may not include `order`
     */
    protected function setOrder($query, $options)
    {
        $order = Arr::get($options, 'order');

        if (! $order) {
            return $query;
        }

        // Just a single ascending value
        if (! is_array($order)) {
            return $query->order($options['order']);
        }

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
            } else {
                // Ascending column
                $query->order($orderValue);
            }
        }

        return $query;
    }

    protected function getPaginationData($query, $options)
    {
        // Get pagination options
        $page = Arr::get($options, 'page');
        $page = ((int)$page > 1 ? $page : 1); // Can't be less than 1
        $resultsPerPage = Arr::get($options, 'resultsPerPage', $this->resultsPerPage);

        // Get total results
        $queryClone = clone $query;
        $queryClone->columns(['count' => new Expression('COUNT(*)')]);
        $statement = $this->sql()->prepareStatementForSqlObject($queryClone);
        $result = $statement->execute()->current();
        $resultCount = $result['count'];
        $pageCount = ceil($resultCount / $resultsPerPage);

        return new PaginationData([
            'page'             => $page,
            'page_count'       => $pageCount,
            'result_count'     => $resultCount,
            'results_per_page' => $resultsPerPage
        ]);
    }

    /**
     * Add where clauses to query
     *
     * @param Zend\Db\Sql\Select $query
     * @param array              $wheres An array of where conditions in the format:
     *                                   ['column' => 'value'] or
     *                                   ['column', 'operator', 'value']
     * @throws InvalidArgumentException  If a WHERE requirement is in an unsupported format.
     */
    protected function addWheres($query, $wheres)
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
                }

                $query->where($predicate);
            } else {
                $query->where([$key => $where]);
            }
        }
    }
}
