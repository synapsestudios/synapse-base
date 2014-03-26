<?php

namespace Synapse\Command\Upgrade;

use Synapse\Command\AbstractDatabaseCommand;
use Zend\Db\Adapter\Adapter as DbAdapter;

/**
 * Abstract class for upgrade commands
 */
abstract class AbstractUpgradeCommand extends AbstractDatabaseCommand
{
    /**
     * Returns the current database version.
     * Assumes that the most recent upgrade is the current database version.
     * (You should always construct and apply upgrades in numerical versioning order.)
     *
     * @return string
     */
    protected function currentDatabaseVersion()
    {
        $version = $this->db->query(
            'SELECT `version` FROM `app_versions` ORDER BY `timestamp` DESC LIMIT 1',
            DbAdapter::QUERY_MODE_EXECUTE
        )->current();

        return $version['version'];
    }
}
