<?php

namespace Test\Synapse\Mapper;

use Synapse\Mapper as MapperNamepace;

class TimestampColumnMapper extends MapperNamepace\AbstractMapper
{
    use MapperNamepace\InserterTrait;
    use MapperNamepace\UpdaterTrait;

    /**
     * {@inheritdoc}
     */
    protected $createdTimestampColumn = 'created';

    /**
     * {@inheritdoc}
     */
    protected $updatedTimestampColumn = 'updated';

    /**
     * {@inheritdoc}
     */
    protected $tableName = 'table_with_timestamp_columns';
}
