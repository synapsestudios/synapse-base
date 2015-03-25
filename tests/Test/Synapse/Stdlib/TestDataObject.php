<?php

namespace Test\Synapse\Stdlib;

use Synapse\Stdlib\DataObject;

class TestDataObject extends DataObject
{
    protected $object = [
        'string'  => null,
        'integer' => null,
        'boolean' => null,
        'foo'     => null,
    ];

    public function setString($value)
    {
        $this->setAsString('string', $value);
    }

    public function setInteger($value)
    {
        $this->setAsInteger('integer', $value);
    }

    public function setBoolean($value)
    {
        $this->setAsBoolean('boolean', $value);
    }
}
