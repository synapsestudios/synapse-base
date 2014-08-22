<?php

namespace Test\Synapse\CliCommand;

use Exception;
use PHPUnit_Framework_TestCase;

use Synapse\CliCommand\AbstractCliCommand;

class CliCommand extends AbstractCliCommand
{
    public function getBaseCommand()
    {
        return 'echo "success"';
    }
}
