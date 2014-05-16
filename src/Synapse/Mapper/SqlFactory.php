<?php

namespace Synapse\Mapper;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Sql;

class SqlFactory
{
    public function getSqlObject(AdapterInterface $adapter, $table = null)
    {
        return new Sql($adapter, $table);
    }
}
