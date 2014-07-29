<?php

namespace Synapse\Debug;

trait DebugModeAwareTrait
{
    /**
     * Whether the app is in debug mode
     *
     * @var bool
     */
    protected $debug;

    /**
     * Set whether the app is in debug mode
     *
     * @param bool $debug
     */
    public function setDebug($debug)
    {
        $this->debug = (bool) $debug;
    }
}
