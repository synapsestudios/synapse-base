<?php

namespace Synapse\Log;

use Monolog\Logger;

interface LoggerAwareInterface
{
    /**
     * @param Logger $logger
     */
    public function setLogger(Logger $logger);
}
