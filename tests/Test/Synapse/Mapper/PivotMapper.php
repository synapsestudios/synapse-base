<?php

namespace Test\Synapse\Mapper;

use Synapse\Mapper as MapperNamespace;

/**
 * Generic mapper for testing
 */
class PivotMapper extends MapperNamespace\AbstractMapper
{
    use MapperNamespace\PivotInserterTrait;
    use MapperNamespace\PivotDeleterTrait;

    /**
     * {@inheritdoc}
     */
    protected $tableName = 'pivot_table';
}
