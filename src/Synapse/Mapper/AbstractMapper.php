<?php

namespace Synapse\Mapper;

use ArrayObject;

use Synapse\Stdlib\Arr;
use Synapse\Entity\AbstractEntity as AbstractEntity;
use Synapse\Db\ResultSet\HydratingResultSet;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\PreparableSqlInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\Hydrator\ArraySerializable;

/**
 * An abstract class for mapping database records to entity objects
 */
abstract class AbstractMapper
{
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
     * Set injected objects as properties
     *
     * @param DbAdapter      $db        Query builder object
     * @param AbstractEntity $prototype Entity prototype
     */
    public function __construct(DbAdapter $dbAdapter, AbstractEntity $prototype = null)
    {
        $this->dbAdapter = $dbAdapter;
        $this->prototype = $prototype;
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
     * Return the entity prototype
     *
     * @return AbstractEntity
     */
    public function getPrototype()
    {
        return $this->prototype;
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
    }

    /**
     * Execute a given query
     *
     * @param  PreparableSqlInterface $query Query to be executed
     * @return Zend\Db\ResultSet\ResultSet
     */
    protected function execute(PreparableSqlInterface $query)
    {
        $this->initialize();

        $statement = $this->sql()->prepareStatementForSqlObject($query);

        $resultSet = new HydratingResultSet($this->hydrator, $this->prototype);
        return $resultSet->initialize($statement->execute());
    }

    /**
     * Return a new Sql object with Zend Db Adapter and table name injected
     *
     * @return Sql
     */
    protected function sql()
    {
        return new Sql($this->dbAdapter, $this->tableName);
    }
}
