<?php

namespace Synapse\Migration;

use Synapse\Command\CommandProxy;

class CreateMigrationCommandProxy extends CommandProxy
{
    /**
     * Set the console command's name and description
     */
    protected function configure()
    {
        $this->setDescription('Run all new database migrations');
    }
}
