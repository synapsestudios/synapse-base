<?php

namespace Synapse\Migration;

use Symfony\Component\Console\Input\InputArgument;
use Synapse\Command\CommandProxy;

class CreateMigrationCommandProxy extends CommandProxy
{
    /**
     * Set name, description, arguments, and options for this console command
     */
    protected function configure()
    {
        $this->setDescription('Create a new database migration')
            ->addArgument(
                'description',
                InputArgument::REQUIRED,
                'Enter a short description of the migration: '
            );
    }
}
