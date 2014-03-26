<?php

namespace Synapse\Command;

use Symfony\Component\Console\Command\Command;
use Zend\Db\Adapter\Adapter as DbAdapter;

/**
 * Abstract class for commands that must access the database
 */
class AbstractDatabaseCommand extends Command
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
