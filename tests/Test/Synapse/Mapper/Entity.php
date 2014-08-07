<?php

namespace Test\Synapse\Mapper;

use Synapse\Entity\AbstractEntity;

class Entity extends AbstractEntity
{
    protected $object = [
        'foo' => null,
        'bar' => null,
        'baz' => null,
    ];
}
