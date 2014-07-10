<?php

namespace Synapse\Install;

use Synapse\Command\CommandProxy;
use Symfony\Component\Console\Input\InputOption;

class RunInstallCommandProxy extends CommandProxy
{
    /**
     * Configure this console command
     */
    protected function configure()
    {
        $this->setDescription('Perform fresh install of the app if necessary')
			->addOption(
			    'drop-tables',
			    null,
			    InputOption::VALUE_NONE,
			    'Forces a fresh install'
			);
    }
}
