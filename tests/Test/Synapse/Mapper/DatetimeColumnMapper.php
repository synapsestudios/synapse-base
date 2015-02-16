<?php

namespace Test\Synapse\Mapper;

use Synapse\Mapper as MapperNamepace;

class DatetimeColumnMapper extends MapperNamepace\AbstractMapper
{
    use MapperNamepace\InserterTrait;
    use MapperNamepace\UpdaterTrait;

    /**
     * {@inheritdoc}
     */
    protected $createdDatetimeColumn = 'created';

    /**
     * {@inheritdoc}
     */
    protected $updatedDatetimeColumn = 'updated';

    /**
     * {@inheritdoc}
     */
    protected $tableName = 'table_with_datetime_columns';
}
