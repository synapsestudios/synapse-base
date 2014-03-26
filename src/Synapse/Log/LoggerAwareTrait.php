<?php

namespace Synapse\Log;

use Monolog\Logger;

trait LoggerAwareTrait
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Set the logger in the instance
     * @param Logger $logger the application logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
        return $this;
    }
}
