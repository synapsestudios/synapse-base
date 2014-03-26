<?php

namespace Synapse\Migration;

use Zend\Db\Adapter\Adapter as DbAdapter;

/**
 * Abstract migration class to be extended by all migrations
 */
abstract class AbstractMigration
{
    /**
     * Description of this migration, to record in the database when it is run
     *
     * @var string
     */
    protected $description;

    /**
     * Timestamp of when this migration was created
     *
     * @var string
     */
    protected $timestamp;

    /**
     * Run database queries to apply this migration
     *
     * @param  DbAdapter $db
     */
    abstract public function execute(DbAdapter $db);

    /**
     * @return string Description of this migration
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string Timestamp of this migration
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
}
