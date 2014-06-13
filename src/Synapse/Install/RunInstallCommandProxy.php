<?php

namespace Synapse\Install;

use Synapse\Command\CommandProxy;

class GenerateInstallCommandProxy extends CommandProxy
{
    /**
     * Configure this console command
     */
    protected function configure()
    {
        $this->setDescription('Perform fresh install of the app (WARNING: drops tables)');
    }
}
