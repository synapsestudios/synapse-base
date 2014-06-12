<?php

namespace Test\Synapse\Mapper;

use Synapse\Entity\AbstractEntity;

/**
 * Entity for testing that timestamp columns are set correctly for inserter and updater traits
 */
class TimestampColumnEntity extends AbstractEntity
{
    /**
     * {@inheritdoc}
     */
    protected $object = [
        'id'      => null,
        'foo'     => null,
        'created' => null,
        'updated' => null,
    ];
}
