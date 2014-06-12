<?php

namespace Test\Synapse\Mapper;

use Synapse\Entity\AbstractEntity;

/**
 * Entity representing a pivot table where there is no ID column
 */
class PivotEntity extends AbstractEntity
{
    /**
     * {@inheritdoc}
     */
    protected $object = [
        'foo_id' => null,
        'bar_id' => null,
    ];
}
