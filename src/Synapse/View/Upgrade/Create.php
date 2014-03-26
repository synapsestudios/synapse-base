<?php

namespace Synapse\View\Upgrade;

use Synapse\View\AbstractView;

/**
 * View for creating new upgrades
 */
class Create extends AbstractView
{
    /**
     * Version of the upgrade
     * @var string
     */
    protected $version;

    /**
     * Version at which this upgrade expects the database to be, in order to run
     * @var string
     */
    protected $expectedVersion;

    /**
     * Name of the upgrade class
     * @var string
     */
    protected $classname;

    /**
     * Set or get the version of the upgrade
     *
     * @param  string $version Upgrade version. If omitted, acts as a getter.
     * @return string
     */
    public function version($version = null)
    {
        if ($version === null) {
            return $this->version;
        }

        $this->version = $version;
    }

    /**
     * Set or get the expected version of the upgrade
     *
     * @param  string $expectedVersion Version this upgrade expects the database to be. If omitted, acts as a getter.
     * @return string
     */
    public function expectedVersion($expectedVersion = null)
    {
        if ($expectedVersion === null) {
            return $this->expectedVersion;
        }

        $this->expectedVersion = $expectedVersion;
    }

    /**
     * Set or get the name of the upgrade class
     *
     * @param  string $classname Name of the upgrade class. If omitted, acts as a getter.
     * @return string
     */
    public function classname($classname = null)
    {
        if ($classname === null) {
            return $this->classname;
        }

        $this->classname = $classname;
    }
}
