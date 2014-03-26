<?php

namespace Synapse\Config;

class Config
{
    protected $readers = [];
    protected $groups  = [];

    /**
     * Attaches a new config reader. Readers are accessed in order by their
     * priority. By default, their priority is the order in which they were
     * attached. Calling Config::attach($reader) will cause any configs read by
     * it to override any configs read by previously attached readers.
     *
     * @param  Reader  $reader
     * @param  boolean $last
     * @return [type]
     */
    public function attach(ReaderInterface $reader, $last = true)
    {
        if ($last) {
            // Add to the end (i.e. read this last, highest merge priority)
            $this->readers[] = $reader;
        } else {
            // Add to the beginning (i.e. read this first, lowest merge priority)
            array_unshift($this->readers, $reader);
        }

        // Clear any cached groups
        $this->groups = [];
    }

    public function detach(ReaderInterface $reader)
    {
        if (($key = array_search($reader, $this->readers)) !== false) {
            unset($this->readers[$key]);
        }

        // Clear any cached groups
        $this->groups = [];
    }

    public function getReaders()
    {
        return $this->readers;
    }

    public function load($groupName)
    {
        if (! count($this->readers)) {
            throw new \RuntimeException('No config readers attached');
        }

        if (! $groupName) {
            throw new \InvalidArgumentException('No config group specified');
        }

        if (! is_string($groupName)) {
            throw new \InvalidArgumentException('Config group must be a string');
        }

        if (isset($this->groups[$groupName])) {
            return $this->groups[$groupName];
        }

        $config = [];

        foreach ($this->readers as $reader) {
            if ($groupConfig = $reader->load($groupName)) {
                $config = array_replace_recursive($config, $groupConfig);
            }
        }

        $this->groups[$groupName] = $config;

        return $this->groups[$groupName];
    }
}
