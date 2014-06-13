<?php

namespace Synapse\Resque;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Synapse\Command\CommandProxy;

class ResqueCommandProxy extends CommandProxy
{
    /**
     * Configure command arguments and options
     */
    protected function configure()
    {
        $this->setDescription('Control worker processes')
            ->addArgument(
                'queue',
                InputArgument::IS_ARRAY,
                'Which queues should the worker process(es) watch? (comma-separated)'
            )
            ->addOption(
                'interval',
                null,
                InputOption::VALUE_REQUIRED,
                'How often should the workers check for new jobs? (seconds)',
                5
            )
            ->addOption(
                'count',
                null,
                InputOption::VALUE_REQUIRED,
                'How many worker processes should run?',
                1
            )
            ->addOption(
                'shutdown',
                null,
                InputOption::VALUE_NONE,
                'Specify this option to shut down the workers',
                null
            );
    }
}
