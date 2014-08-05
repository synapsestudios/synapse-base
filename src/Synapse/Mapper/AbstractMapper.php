<?php

namespace Synapse\Mapper;

use ArrayObject;

use Synapse\Stdlib\Arr;
use Synapse\Entity\AbstractEntity as AbstractEntity;
use Synapse\Entity\EntityIterator;
use Synapse\Db\ResultSet\HydratingResultSet;
use Synapse\Log\LoggerAwareInterface;
use Synapse\Log\LoggerAwareTrait;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\PreparableSqlInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\Hydrator\ArraySerializable;

/**
 * An abstract class for mapping database records to entity objects
 */
abstract class AbstractMapper implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Whether the object has been initialized yet
     *
     * @var boolean
     */
    protected $initialized = false;

    /**
     * Database adapter
     *
     * @var DbAdapter
     */
    protected $dbAdapter;

    /**
     * Entity prototype used to return hydrated entities
     *
     * @var Entity
     */
    protected $prototype;

    /**
     * Name of the table for which this mapper is responsible
     *
     * @var string
     */
    protected $tableName;

    /**
     * The hydrator used to create entities from database results
     *
     * @var Zend\Stdlib\Hydrator\HydratorInterface
     */
    protected $hydrator;

    /**
     * Factory that creates SQL objects
     *
     * @var SqlFactory
     */
    protected $sqlFactory;

    /**
     * The name of the column where a time created timestamp is stored
     *
     * @var string
     */
    protected $createdTimestampColumn = null;

    /**
     * The name of the column where a time updated timestamp is stored
     *
     * @var string
     */
    protected $updatedTimestampColumn = null;

    /**
     * Set injected objects as properties
     *
     * @param DbAdapter      $db        Query builder object
     * @param AbstractEntity $prototype Entity prototype
     */
    public function __construct(DbAdapter $dbAdapter, AbstractEntity $prototype = null)
    {
        $this->dbAdapter = $dbAdapter;
        $this->prototype = $prototype;

        $this->initialize();
    }

    /**
     * Set the SqlFactory object used in getSqlObject
     *
     * @param SqlFactory $sqlFactory
     */
    public function setSqlFactory(SqlFactory $sqlFactory)
    {
        $this->sqlFactory = $sqlFactory;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return DbAdapter
     *
     * @codeCoverageIgnore
     */
    public function getDbAdapter()
    {
        return $this->dbAdapter;
    }

    /**
     * Persist this entity, inserting it if new and otherwise updating it
     *
     * @param  AbstractEntity $entity
     * @return AbstractEntity
     */
    public function persist(AbstractEntity $entity)
    {
        if ($entity->isNew()) {
            return $this->insert($entity);
        }

        return $this->update($entity);
    }

    /**
     * Return a clone of the entity prototype
     *
     * @return AbstractEntity
     */
    public function getPrototype()
    {
        return clone $this->prototype;
    }

    /**
     * Set the entity prototype for this mapper
     *
     * @param AbstractEntity $prototype
     */
    public function setPrototype(AbstractEntity $prototype)
    {
        $this->prototype = $prototype;
        return $this;
    }

    protected function initialize()
    {
        if ($this->initialized) {
            return;
        }

        if (!is_object($this->prototype)) {
            $this->prototype = new ArrayObject;
        }

        if (!$this->hydrator instanceof HydratorInterface) {
            $this->hydrator = new ArraySerializable;
        }

        $this->initialized = true;
    }

    /**
     * Execute a given query
     *
     * @param  PreparableSqlInterface $query Query to be executed
     * @return Zend\Db\ResultSet\ResultSet
     */
    protected function execute(PreparableSqlInterface $query)
    {
        $statement = $this->getSqlObject()->prepareStatementForSqlObject($query);

        $resultSet = new HydratingResultSet($this->hydrator, $this->prototype);
        return $resultSet->initialize($statement->execute());
    }

    /**
     * Execute a given query and return the result as a single Entity object
     *
     * @param  PreparableSqlInterface $query Query to be executed
     * @return AbstractEntity
     */
    protected function executeAndGetResultsAsEntity(PreparableSqlInterface $query){
        $data = $this->execute($query)->current();

        if (! $data || count($data) === 0) {
            return false;
        }

        return $data;
    }

    /**
     * Execute a given query and return the result as a Entity Iterator object
     *
     * @param  PreparableSqlInterface $query Query to be executed
     * @return EntityIterator
     */
    protected function executeAndGetResultsAsEntityIterator(PreparableSqlInterface $query){
        $entities = $this->execute($query)
            ->toEntityArray();

        return new EntityIterator($entities);
    }

    /**
     * Rename to getSqlObject, but this method kept for backwards compatibility
     *
     * @return Sql
     *
     * @codeCoverageIgnore
     */
    protected function sql()
    {
        return $this->getSqlObject();
    }

    /**
     * Return a new Sql object with Zend Db Adapter and table name injected
     *
     * @return Sql
     */
    protected function getSqlObject()
    {
        // For backwards compatibility, support the old unmockable way
        if (! $this->sqlFactory) {
            return new Sql($this->dbAdapter, $this->tableName);
        }

        return $this->sqlFactory->getSqlObject(
            $this->dbAdapter,
            $this->tableName
        );
    }
}
