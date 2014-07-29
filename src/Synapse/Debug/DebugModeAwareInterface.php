<?php

namespace Synapse\Debug;

interface DebugModeAwareInterface
{
    /**
     * Set whether the app is in debug mode
     *
     * @param bool $debug
     */
    public function setDebug($debug);
}
