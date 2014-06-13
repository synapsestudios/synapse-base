<?php

namespace Synapse\Command;

use Zend\Db\Adapter\Adapter as DbAdapter;

/**
 * Abstract class for commands that must access the database
 */
class AbstractDatabaseCommand implements CommandInterface
{
    /**
     * Database adapter
     *
     * @var Zend\Db\Adapter\Adapter
     */
    protected $db;

    /**
     * Set the database adapter
     *
     * @param \Zend\Db\Adapter\Adapter $db
     */
    public function setDatabaseAdapter(DbAdapter $db)
    {
        $this->db = $db;
    }
}
