<?php

namespace Synapse\Upgrade;

use Zend\Db\Adapter\Adapter as DbAdapter;

/**
 * Abstract upgrade class to be extended by all upgrades
 */
abstract class AbstractUpgrade
{
    /**
     * Execute the upgrade
     */
    abstract public function execute(DbAdapter $db);
}
