<?php

namespace Synapse\Config;

interface ReaderInterface
{
    /**
     * Load a config namespace from the given directory
     *
     * @param  string $namespace config namespace to load
     * @return array  the config that was read
     */
    public function load($namespace);
}
