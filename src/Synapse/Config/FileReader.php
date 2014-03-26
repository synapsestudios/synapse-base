<?php

namespace Synapse\Config;

class FileReader implements ReaderInterface
{
    /**
     * Directory to search for config files
     * @var string
     */
    protected $directory;

    /**
     * The configuration directory to load configs from
     * @param string $directory path to configs
     */
    public function __construct($directory)
    {
        $this->directory = $directory;
    }

    /**
     * Load a config namespace from the given directory
     *
     * @param  string $namespace config namespace to load
     * @return array  the config that was read
     */
    public function load($namespace)
    {
        if (! $namespace) {
            throw new \InvalidArgumentException('No config namespace provided');
        }

        $filename = realpath($this->directory).'/'.$namespace.'.php';

        if (! file_exists($filename)) {
            return [];
        }

        return include $filename;
    }
}
