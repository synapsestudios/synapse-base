<?php

namespace Synapse\Db\ResultSet;

use Zend\Db\ResultSet\HydratingResultSet as ZendHydratingResultSet;

class HydratingResultSet extends ZendHydratingResultSet
{
    /**
     * Similar to toArray, but returns an array of entities instead of an array
     * of arrays (extracted entities).
     *
     * This is useful (and necessary) because PDO result sets are not buffered
     * (immediately sent to and stored in the memory of the PHP process)
     * like ext_mysql or mysqli results are.
     *
     * The result of this is that you cannot rewind these iterables (or foreach)
     * across them without first copying them to an array.
     *
     * @return array of entities
     */
    public function toEntityArray()
    {
        $return = [];
        foreach ($this as $row) {
            $return[] = $row;
        }
        return $return;
    }
}
