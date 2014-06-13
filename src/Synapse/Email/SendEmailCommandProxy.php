<?php

namespace Synapse\Email;

use Synapse\Command\CommandProxy;
use Symfony\Component\Console\Input\InputArgument;

class SendEmailCommandProxy extends CommandProxy
{
    /**
     * Set name, description, arguments, and options for this console command
     */
    protected function configure()
    {
        $this->setDescription('Send an email')
            ->addArgument(
                'id',
                InputArgument::REQUIRED,
                'ID of email to send'
            );
    }
}
