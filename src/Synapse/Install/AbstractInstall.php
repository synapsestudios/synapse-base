<?php

namespace Synapse\Install;

use Zend\Db\Adapter\Adapter as DbAdapter;

/**
 * Abstract upgrade class to be extended by all upgrades
 */
abstract class AbstractInstall
{
    /**
     * Execute the upgrade
     */
    abstract public function execute(DbAdapter $db);
}
