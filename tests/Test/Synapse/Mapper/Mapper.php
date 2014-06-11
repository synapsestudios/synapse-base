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

    /**
     * {@inheritdoc}
     */
    protected $tableName = 'test_table';
}
