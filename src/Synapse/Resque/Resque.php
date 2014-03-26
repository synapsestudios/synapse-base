<?php

namespace Synapse\Resque;

use Synapse\Stdlib\Arr;
use Resque as ResqueLib;

/**
 * Wrapper class for Resque
 */
class Resque
{
    /**
     * Initialize Resque
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        ResqueLib::setBackend(Arr::get($config, 'host'));
    }

    /**
     * Enqueue a Resque job
     *
     * @param  string  $queue        The queue in which to place the job
     * @param  string  $class        The work class to be executed
     * @param  array   $args         Arguments
     * @param  bool    $track_status Whether to track the status of the job
     * @return string
     */
    public function enqueue($queue, $class, $args = null, $track_status = false)
    {
        ResqueLib::enqueue($queue, $class, $args, $track_status);
    }
}
