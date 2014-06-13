<?php

namespace Synapse\Install;

use Synapse\Command\CommandProxy;

class GenerateInstallCommandProxy extends CommandProxy
{
    /**
     * Set name, description, arguments, and options for this console command
     */
    protected function configure()
    {
        $this->setDescription('Generate database install files to match the current database');
    }
}
