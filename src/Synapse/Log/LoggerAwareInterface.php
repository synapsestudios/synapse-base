<?php

namespace Synapse\Log;

use Monolog\Logger;

interface LoggerAwareInterface
{
    public function setLogger(Logger $logger);
}
