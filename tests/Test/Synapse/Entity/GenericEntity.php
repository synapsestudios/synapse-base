<?php

namespace Test\Synapse\Entity;

use Synapse\Entity\AbstractEntity;

class GenericEntity extends AbstractEntity
{
    protected $object = [
        'foo'      => null,
        'baz'      => null,
        'default1' => 1,
    ];
}
