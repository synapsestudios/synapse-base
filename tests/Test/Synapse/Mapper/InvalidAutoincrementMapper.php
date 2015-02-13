<?php

namespace Test\Synapse\Mapper;

use Synapse\Mapper as MapperNamespace;

/**
 * Generic mapper for testing
 */
class InvalidAutoincrementMapper extends MapperNamespace\AbstractMapper
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

    protected $autoIncrementColumn = 'not_a_real_column';
}
