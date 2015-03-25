<?php

namespace Test\Synapse\Mapper;

use Synapse\Mapper as MapperNamespace;

/**
 * Generic mapper for testing
 */
class Mapper extends MapperNamespace\AbstractMapper
{
    use MapperNamespace\FinderTrait;
    use MapperNamespace\InserterTrait;
    use MapperNamespace\UpdaterTrait;
    use MapperNamespace\DeleterTrait;

    const OTHER_TABLE = 'other_table';

    /**
     * {@inheritdoc}
     */
    protected $tableName = 'test_table';

    public function queryAlternateTable()
    {
        $sql = $this->sqlFactory->getSqlObject($this->dbAdapter, self::OTHER_TABLE);

        $sql->select()
            ->where(['foo' => 'bar']);
    }

    public function performQueryAndGetResultsAsArray()
    {
        $query = $this->getSqlObject()
            ->select()
            ->where(['foo' => 'bar']);

        return $this->executeAndGetResultsAsArray($query);
    }
}
